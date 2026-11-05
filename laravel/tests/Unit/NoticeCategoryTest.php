<?php

namespace Tests\Unit;

use App\Enums\NoticeCategory;
use PHPUnit\Framework\TestCase;

class NoticeCategoryTest extends TestCase
{
    public function test_every_category_has_a_label_and_a_badge(): void
    {
        foreach (NoticeCategory::cases() as $category) {
            $this->assertNotSame('', $category->label());
            $this->assertNotSame('', $category->badge());
        }
    }

    public function test_unknown_values_are_rejected(): void
    {
        $this->assertNull(NoticeCategory::tryFrom('aliens'));
        $this->assertSame(NoticeCategory::Events, NoticeCategory::from('events'));
    }
}
