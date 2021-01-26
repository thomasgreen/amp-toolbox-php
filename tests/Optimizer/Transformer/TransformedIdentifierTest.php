<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\Configuration\TransformedIdentifierConfiguration;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Tests\MarkupComparison;
use AmpProject\Tests\TestCase;
use AmpProject\Tests\TestMarkup;

/**
 * Test the TransformedIdentifier transformer.
 *
 * @covers \AmpProject\Optimizer\Transformer\TransformedIdentifier
 * @package ampproject/amp-toolbox
 */
final class TransformedIdentifierTest extends TestCase
{
    use MarkupComparison;

    /**
     * Provide the data to test the transform() method.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataTransform()
    {
        $input = static function ($html) {
            return TestMarkup::DOCTYPE . $html . '<head>'
                   . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                   . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE
                   . '</head><body></body></html>';
        };

        $expected = static function ($html) {
            return TestMarkup::DOCTYPE . $html . '<head>'
                   . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                   . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE
                   . '</head><body></body></html>';
        };

        return [
            'adds identifier with default version to html tag' => [
                $input('<html ⚡>'),
                $expected('<html ⚡ transformed="self;v=1">'),
            ],

            'adds identifier with custom version to html tag' => [
                $input('<html ⚡>'),
                $expected('<html ⚡ transformed="self;v=5">'),
                5,
            ],

            'adds identifier without version to html tag' => [
                $input('<html ⚡>'),
                $expected('<html ⚡ transformed="self">'),
                0,
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \AmpProject\Optimizer\Transformer\TransformedIdentifier::transform()
     * @dataProvider dataTransform()
     *
     * @param string   $source       String of source HTML.
     * @param string   $expectedHtml String of expected HTML output.
     * @param int|null $version      Version to use. Null to not specify a specific one and fall back to default.
     */
    public function testTransform($source, $expectedHtml, $version = null)
    {
        $document = Document::fromHtml($source);
        $config   = [];
        if ($version !== null) {
            $config = [TransformedIdentifierConfiguration::VERSION => $version];
        }
        $transformer = new TransformedIdentifier(new TransformedIdentifierConfiguration($config));
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertEqualMarkup($expectedHtml, $document->saveHTML());
    }
}
