<?php

use App\Libraries\EditorJsRenderer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EditorJsRendererTest extends CIUnitTestCase
{
    private EditorJsRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new EditorJsRenderer();
    }

    public function testRendersAllowedInlineTags(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => 'Ahoj <b>světe</b> a <mark>zvýraznění</mark> a <a href="https://example.com">odkaz</a>.',
                ],
            ],
        ]));

        $this->assertStringContainsString('<p>', $html);
        $this->assertStringContainsString('<b>světe</b>', $html);
        $this->assertStringContainsString('<mark>zvýraznění</mark>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
    }

    public function testStripsScriptsAndImages(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => 'Text <script>alert(1)</script> a <img src="x.jpg" alt="x"> konec.',
                ],
            ],
        ]));

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('Text', $html);
        $this->assertStringContainsString('konec.', $html);
    }

    public function testRemovesJavascriptLinks(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => '<a href="javascript:alert(1)">klik</a>',
                ],
            ],
        ]));

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('href=', $html);
    }

    public function testKeepsSafeLinkTargetAndRel(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => '<a href="https://example.com" target="_blank" rel="nofollow">odkaz</a>',
                ],
            ],
        ]));

        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="', $html);
        $this->assertStringContainsString('nofollow', $html);
        $this->assertStringContainsString('noopener', $html);
        $this->assertStringContainsString('noreferrer', $html);
    }

    public function testDropsUnsafeLinkTargetAndRel(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => '<a href="https://example.com" target="javascript:alert(1)" rel="evil">odkaz</a>',
                ],
            ],
        ]));

        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('evil', $html);
        $this->assertStringNotContainsString('target=', $html);
    }

    public function testDropsUnknownBlocks(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'image',
                'data' => ['url' => 'https://evil.example/a.png'],
            ],
            [
                'type' => 'paragraph',
                'data' => ['text' => 'Jen odstavec'],
            ],
        ]));

        $this->assertStringNotContainsString('evil.example', $html);
        $this->assertStringContainsString('Jen odstavec', $html);
    }

    public function testRendersNestedLists(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'list',
                'data' => [
                    'style' => 'unordered',
                    'items' => [
                        [
                            'content' => 'První',
                            'items'   => [
                                ['content' => 'Vnořená', 'items' => []],
                            ],
                        ],
                        'Druhá',
                    ],
                ],
            ],
        ]));

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>První', $html);
        $this->assertStringContainsString('Vnořená', $html);
        $this->assertStringContainsString('<li>Druhá</li>', $html);
    }

    public function testRendersOrderedListStartAndCounterType(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'list',
                'data' => [
                    'style' => 'ordered',
                    'meta'  => [
                        'start'       => 4,
                        'counterType' => 'upper-roman',
                    ],
                    'items' => [
                        [
                            'content' => 'První',
                            'meta'    => [],
                            'items'   => [
                                ['content' => 'Vnořená', 'meta' => [], 'items' => []],
                            ],
                        ],
                        ['content' => 'Druhá', 'meta' => [], 'items' => []],
                    ],
                ],
            ],
        ]));

        $this->assertStringContainsString('<ol start="4" data-counter="upper-roman">', $html);
        $this->assertStringContainsString('<ol data-counter="upper-roman">', $html);
        $this->assertStringNotContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>První', $html);
        $this->assertStringContainsString('Vnořená', $html);
    }

    public function testIgnoresInvalidOrderedListMeta(): void
    {
        $html = $this->renderer->render($this->document([
            [
                'type' => 'list',
                'data' => [
                    'style' => 'ordered',
                    'meta'  => [
                        'start'       => 'abc',
                        'counterType' => 'javascript:alert(1)',
                    ],
                    'items' => ['Jedna'],
                ],
            ],
        ]));

        $this->assertStringContainsString('<ol>', $html);
        $this->assertStringNotContainsString('start=', $html);
        $this->assertStringNotContainsString('data-counter', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function testPlainTextStripsMarkupAndTruncatesOnWord(): void
    {
        $short = $this->renderer->plainText($this->document([
            [
                'type' => 'paragraph',
                'data' => ['text' => 'Ahoj <b>světe</b>.'],
            ],
        ]));

        $this->assertSame('Ahoj světe.', $short);

        $long = $this->renderer->plainText($this->document([
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => 'První slovo druhé slovo třetí slovo čtvrté slovo páté slovo.',
                ],
            ],
        ]), 28);

        $this->assertSame('První slovo druhé slovo…', $long);
    }

    /**
     * @param list<array<string, mixed>> $blocks
     */
    private function document(array $blocks): string
    {
        return json_encode([
            'time'    => 1,
            'blocks'  => $blocks,
            'version' => '2.30.7',
        ], JSON_THROW_ON_ERROR);
    }
}
