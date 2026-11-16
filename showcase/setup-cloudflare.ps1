# One-time setup for the showcase deployment (Windows PowerShell 5.1 or 7).
#
#   irm https://raw.githubusercontent.com/Kukutx/php-lab/main/showcase/setup-cloudflare.ps1 | iex
#
# 1. Opens the Cloudflare page where you create an API token for the
#    "andrealiu" account (the only account this project may deploy to).
# 2. Asks for the token (hidden input), checks that it can reach exactly
#    that account, and saves it as the GitHub secret CLOUDFLARE_API_TOKEN.
# 3. Starts the "Showcase" workflow and prints the live URL.
#
# The token is never printed and never written to disk. Requires the GitHub CLI (gh), logged in.

$ErrorActionPreference = 'Stop'
$AccountId = '055f054087ae67958826268f493946d5'   # andrealiu - do not change
$Repo      = 'Kukutx/php-lab'
$Project   = 'php-lab'
$Api       = "https://api.cloudflare.com/client/v4/accounts/$AccountId"

function Step($text) { Write-Host "`n== $text" -ForegroundColor Cyan }

Step 'Checking the GitHub CLI'
gh auth status 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'gh is not logged in. Run "gh auth login" first.' }

Step 'Create a Cloudflare API token'
Write-Host @"
A browser tab will open on the andrealiu account (check the account name at the top left).
  1. Click "Create Token" -> "Create Custom Token" (Get started).
  2. Name: php-lab showcase
  3. Permissions: Account | Cloudflare Pages | Edit
  4. Continue to summary -> Create Token, then copy the token.
If that page does not open on andrealiu, use My Profile -> API Tokens instead and set
"Account Resources" to Include -> andrealiu only.
"@
Start-Process "https://dash.cloudflare.com/$AccountId/api-tokens"

$headers = $null
for ($try = 1; $try -le 3 -and -not $headers; $try++) {
    $secure = Read-Host -AsSecureString 'Paste the token here (input is hidden) and press Enter'
    $bstr   = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    $token  = ([Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)).Trim()
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)

    $h = @{ Authorization = "Bearer $token" }
    try {
        $r = Invoke-RestMethod -Uri "$Api/pages/projects" -Headers $h -Method Get
        if ($r.success) { $headers = $h; Write-Host 'Token OK: it can manage Pages on the andrealiu account.' -ForegroundColor Green }
    } catch {
        Write-Host "This token cannot access Pages on account $AccountId. Check the account and the 'Cloudflare Pages: Edit' permission, then try again." -ForegroundColor Yellow
    }
}
if (-not $headers) { throw 'No valid token, nothing was changed.' }

Step 'Saving the token as a GitHub secret'
gh secret set CLOUDFLARE_API_TOKEN --repo $Repo --body $token
if ($LASTEXITCODE -ne 0) { throw 'gh secret set failed.' }
$token = $null

Step 'Starting the deploy workflow'
gh workflow run showcase.yml --repo $Repo --ref main
if ($LASTEXITCODE -ne 0) { throw 'gh workflow run failed.' }
$runId = $null
for ($i = 0; $i -lt 20 -and -not $runId; $i++) {
    Start-Sleep -Seconds 3
    $runId = gh run list --repo $Repo --workflow showcase.yml --event workflow_dispatch --limit 1 --json databaseId,status --jq '.[] | select(.status != "completed") | .databaseId'
}
if (-not $runId) { throw "Could not find the new run. Check https://github.com/$Repo/actions" }
gh run watch $runId --repo $Repo --exit-status
if ($LASTEXITCODE -ne 0) { throw "The workflow failed: https://github.com/$Repo/actions/runs/$runId" }

$p = Invoke-RestMethod -Uri "$Api/pages/projects/$Project" -Headers $headers -Method Get
Write-Host "`nDone. Live site: https://$($p.result.subdomain)" -ForegroundColor Green
