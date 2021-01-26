<?php

namespace AmpProject\Dom;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Exception\MaxCssByteCountExceeded;
use AmpProject\Tag;
use AmpProject\Tests\TestCase;

/**
 * Tests for AmpProject\Dom\Element.
 *
 * @covers \AmpProject\Dom\Element
 * @package ampproject/amp-toolbox
 */
class ElementTest extends TestCase
{
    /**
     * Data provider for testing the byte count property.
     *
     * @return array Testing data.
     */
    public function dataByteCounts()
    {
        return [
            'count_style_attribute' => [
                '<div style="12345"></div><', 5,
            ],

            // Adding <head> tag because of bug (see https://github.com/ampproject/amp-toolbox-php/issues/28).
            'multibyte_chars_are_counted_in_bytes_not_chars' => [
                '<head></head><div style="Iñtërnâtiônàlizætiøn"></div>', 27,
            ],
        ];
    }

    /**
     * Test the byte count property.
     *
     * @dataProvider dataByteCounts
     * @covers \AmpProject\Dom\Element::__get()
     *
     * @param string $html     HTML to test against.
     * @param int    $expected Expected number of bytes of inline styles.
     */
    public function testByteCounts($html, $expected)
    {
        $document = Document::fromHtmlFragment($html);
        /** @var Element $element */
        $element = $document->xpath->query('.//*[@style]', $document->body)->item(0);
        $this->assertEquals($expected, $element->inlineStyleByteCount);
    }

    /**
     * Test adding inline styles without CSS byte count limit.
     *
     * @covers \AmpProject\Dom\Element::addInlineStyle()
     */
    public function testAddInlineStyleWithoutLimit()
    {
        $document = new Document();
        $ampCustomStyle = $document->createElement(Tag::STYLE);
        $ampCustomStyle->setAttribute(Attribute::AMP_CUSTOM, null);
        $ampCustomStyle->textContent = str_pad('', Amp::MAX_CSS_BYTE_COUNT - 38, 'X');
        $document->head->appendChild($ampCustomStyle);

        /** @var Element $element */
        $element = $document->createElement(Tag::DIV);
        $document->body->appendChild($element);

        // Inline style can be added.
        $element->addInlineStyle('color:red');

        $this->assertEquals('<div style="color:red"></div>', (string)new ElementDump($element));

        // Semicolons are handled automatically.
        $element->addInlineStyle('  ;  ;  border-left=0  ;  ;  ');
        $element->addInlineStyle('  ;  ;  border-right=0  ;  ;  ');

        $this->assertEquals('<div style="color:red;border-left=0;border-right=0"></div>', (string)new ElementDump($element));

        $element->addInlineStyle('XXXXX');

        $this->assertEquals('<div style="color:red;border-left=0;border-right=0;XXXXX"></div>', (string)new ElementDump($element));
    }

    /**
     * Test adding inline styles with CSS byte count limit.
     *
     * @covers \AmpProject\Dom\Element::addInlineStyle()
     */
    public function testAddInlineStyleWithLimit()
    {
        $document = new Document();
        $document->enforceCssMaxByteCount(Amp::MAX_CSS_BYTE_COUNT);
        $ampCustomStyle = $document->createElement(Tag::STYLE);
        $ampCustomStyle->setAttribute(Attribute::AMP_CUSTOM, null);
        $ampCustomStyle->textContent = str_pad('', Amp::MAX_CSS_BYTE_COUNT - 38, 'X');
        $document->head->appendChild($ampCustomStyle);

        /** @var Element $element */
        $element = $document->createElement(Tag::DIV);
        $document->body->appendChild($element);

        // Inline style can be added.
        $element->addInlineStyle('color:red');

        $this->assertEquals('<div style="color:red"></div>', (string)new ElementDump($element));

        // Semicolons are handled automatically.
        $element->addInlineStyle('  ;  ;  border-left=0  ;  ;  ');
        $element->addInlineStyle('  ;  ;  border-right=0  ;  ;  ');

        $this->assertEquals('<div style="color:red;border-left=0;border-right=0"></div>', (string)new ElementDump($element));

        // Exception is thrown if maximum allowed byte count is exceeded.
        $this->expectException(MaxCssByteCountExceeded::class);
        $this->expectExceptionMessage(
            'Maximum allowed CSS byte count exceeded for inline style'
        );

        $element->addInlineStyle('X');
    }
}
