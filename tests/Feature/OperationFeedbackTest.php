<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OperationFeedbackTest extends TestCase
{
    /** @return array<string, array{string, string, string}> */
    public static function operationMessages(): array
    {
        return [
            'cadastro concluído' => ['status', 'status', 'Cliente cadastrado com sucesso.'],
            'exclusão recusada' => ['error', 'alert', 'Clientes vinculados a notas fiscais não podem ser excluídos.'],
        ];
    }

    #[DataProvider('operationMessages')]
    public function test_customer_page_shows_the_operation_result_once_in_the_global_region(string $key, string $role, string $message): void
    {
        $this->actingAs(User::factory()->make());
        $this->withSession([$key => $message]);

        $page = $this->view('customers.customers', ['customers' => collect()]);

        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">'.$page, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);
        $notice = $xpath->query("//body/*[@data-toast-region]/*[@role='{$role}']//*[contains(@class, 'jemp-notice__message')]");

        $this->assertSame($message, $notice->item(0)?->textContent);
        $this->assertSame(1, substr_count((string) $page, $message));
    }
}
