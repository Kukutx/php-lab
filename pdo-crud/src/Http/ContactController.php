<?php

declare(strict_types=1);

namespace App\Http;

use App\Domain\Category;
use App\Domain\Contact;
use App\Exception\ContactNotFoundException;
use App\Exception\ValidationException;
use App\Repository\ContactRepository;
use App\Validation\ContactValidator;

/**
 * A tiny front controller. Dependencies arrive through the constructor
 * (dependency injection), which is the same idea Laravel and Drupal use
 * with their service containers.
 */
final class ContactController
{
    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly ContactValidator $validator,
        private readonly View $view,
    ) {
    }

    public function handle(string $method, string $path): void
    {
        try {
            if ($method === 'POST' && !Csrf::isValid($_POST['_csrf'] ?? null)) {
                $this->abort(419, 'Invalid CSRF token. Please reload the page and try again.');
                return;
            }

            // match + regex based routing: each route is one line.
            $id = preg_match('#^/contacts/(\d+)#', $path, $m) === 1 ? (int) $m[1] : null;

            match (true) {
                $method === 'GET' && ($path === '/' || $path === '/contacts') => $this->index(),
                $method === 'GET' && $path === '/contacts/new' => $this->form(new Contact('', '')),
                $method === 'POST' && $path === '/contacts' => $this->store(null),
                $method === 'GET' && $id !== null && str_ends_with($path, '/edit')
                    => $this->form($this->contacts->find($id)),
                $method === 'POST' && $id !== null && str_ends_with($path, '/delete') => $this->destroy($id),
                $method === 'POST' && $id !== null => $this->store($id),
                default => $this->abort(404, 'Page not found.'),
            };
        } catch (ContactNotFoundException $e) {
            $this->abort(404, $e->getMessage());
        }
    }

    private function index(): void
    {
        $search = isset($_GET['q']) ? trim((string) $_GET['q']) : null;
        echo $this->view->render('index', [
            'contacts' => $this->contacts->all($search),
            'search' => $search,
            'flash' => $this->pullFlash(),
        ]);
    }

    /** @param array<string, string> $errors */
    private function form(Contact $contact, array $errors = []): void
    {
        echo $this->view->render('form', [
            'contact' => $contact,
            'errors' => $errors,
            'categories' => Category::cases(),
        ]);
    }

    private function store(?int $id): void
    {
        try {
            $contact = $this->validator->validate($_POST, $id);
        } catch (ValidationException $e) {
            http_response_code(422);
            // Re-show the form with what the user typed (not saved).
            $this->form(new Contact(
                (string) ($_POST['name'] ?? ''),
                (string) ($_POST['email'] ?? ''),
                (string) ($_POST['phone'] ?? ''),
                Category::tryFrom((string) ($_POST['category'] ?? '')) ?? Category::Personal,
                $id,
            ), $e->errors);
            return;
        }

        $saved = $this->contacts->save($contact);
        $this->redirect('/contacts', sprintf('Contact "%s" saved.', $saved->name));
    }

    private function destroy(int $id): void
    {
        $this->contacts->delete($id);
        $this->redirect('/contacts', 'Contact deleted.');
    }

    /** Post/Redirect/Get: refreshing the page will not re-submit the form. */
    private function redirect(string $to, string $flash): void
    {
        $_SESSION['flash'] = $flash;
        header('Location: ' . $to, true, 303);
    }

    private function pullFlash(): ?string
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $flash;
    }

    private function abort(int $status, string $message): void
    {
        http_response_code($status);
        echo $this->view->render('error', ['status' => $status, 'message' => $message]);
    }
}
