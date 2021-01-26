<?php

namespace AmpProject;

use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Tests\TestCase;
use DOMNode;

/**
 * Tests for AmpProject\Amp.
 *
 * @covers \AmpProject\Amp
 * @package ampproject/amp-toolbox
 */
class AmpTest extends TestCase
{

    /**
     * Provide data for the testIsRuntimeScript() method.
     *
     * @return array[] Array
     */
    public function dataIsRuntimeScript()
    {
        $dom          = new Document();
        $ampSrc       = 'https://cdn.ampproject.org/v0.js';
        $ampForAdsSrc = 'https://cdn.ampproject.org/amp4ads-v0.js';

        return [
            'amp-runtime'          => [$this->createAmpCDNScript($dom, $ampSrc), true],
            'amp-for-ads-runtime'  => [$this->createAmpCDNScript($dom, $ampForAdsSrc), true],
            'amp-extension-script' => [$this->createExtensionScript($dom, 'amp-runtime'), false],
            'not-a-script'         => [$dom->createElement(Tag::STYLE), false],
            'not-an-element'       => [$dom->createTextNode(Extension::EXPERIMENT), false],
        ];
    }

    /**
     * Test the check for an AMP runtime method.
     *
     * @dataProvider dataIsRuntimeScript
     * @covers       \AmpProject\Amp::isRuntimeScript()
     *
     * @param DOMNode $node     Node to check.
     * @param bool    $expected Expected boolean result.
     */
    public function testIsRuntimeScript($node, $expected)
    {
        $this->assertEquals($expected, Amp::isRuntimeScript($node));
    }

    /**
     * Provide data for the testIsViewerScript() method.
     *
     * @return array[] Array
     */
    public function dataIsViewerScript()
    {
        $dom = new Document();
        $url = 'https://cdn.ampproject.org/v0/amp-viewer-integration-123.js';

        return [
            'amp-viewer'           => [$this->createAmpCDNScript($dom, $url), true],
            'amp-extension-script' => [$this->createExtensionScript($dom, 'amp-viewer'), false],
            'not-a-script'         => [$dom->createElement(Tag::STYLE), false],
            'not-an-element'       => [$dom->createTextNode(Extension::EXPERIMENT), false],
        ];
    }

    /**
     * Test the check for an AMP runtime method.
     *
     * @dataProvider dataIsViewerScript
     * @covers       \AmpProject\Amp::isViewerScript()
     *
     * @param DOMNode $node     Node to check.
     * @param bool    $expected Expected boolean result.
     */
    public function testIsViewerScript($node, $expected)
    {
        $this->assertEquals($expected, Amp::isViewerScript($node));
    }

    /**
     * Provide data for the testIsRenderDelayingExtension() method.
     *
     * @return array[] Array
     */
    public function dataIsRenderDelayingExtension()
    {
        $dom = new Document();

        return [
            Extension::DYNAMIC_CSS_CLASSES => [$this->createExtensionScript($dom, Extension::DYNAMIC_CSS_CLASSES), true],
            Extension::EXPERIMENT          => [$this->createExtensionScript($dom, Extension::EXPERIMENT), true],
            Extension::STORY               => [$this->createExtensionScript($dom, Extension::STORY), true],
            Extension::BIND                => [$this->createExtensionScript($dom, Extension::BIND), false],
            Attribute::CUSTOM_TEMPLATE     => [$this->createExtensionScript($dom, Extension::MUSTACHE, Attribute::CUSTOM_TEMPLATE), false],
            'not-a-script'                 => [$dom->createElement(Tag::STYLE), false],
            'not-an-element'               => [$dom->createTextNode(Extension::EXPERIMENT), false],
        ];
    }

    /**
     * Test the render delaying check method.
     *
     * @dataProvider dataIsRenderDelayingExtension
     * @covers       \AmpProject\Amp::isRenderDelayingExtension()
     *
     * @param DOMNode $node     Node to check
     * @param bool    $expected Expected boolean result.
     */
    public function testIsRenderDelayingExtension($node, $expected)
    {
        $this->assertEquals($expected, Amp::isRenderDelayingExtension($node));
    }

    /**
     * Provide data for the testIsCustomElement() method.
     *
     * @return array[] Array
     */
    public function dataIsCustomElement()
    {
        $dom = new Document();

        return [
            Extension::EXPERIMENT => [$dom->createElement(Extension::EXPERIMENT), true],
            Extension::STORY      => [$dom->createElement(Extension::STORY), true],
            'div'                 => [$dom->createElement('div'), false],
            'custom-amp'          => [$dom->createElement('custom-amp'), false],
        ];
    }

    /**
     * Test the check whether a given node is an AMP custom element.
     *
     * @dataProvider dataIsCustomElement
     * @covers       \AmpProject\Amp::isCustomElement()
     *
     * @param DOMNode $node     Node to check
     * @param bool    $expected Expected boolean result.
     */
    public function testIsCustomElement(DOMNode $node, $expected)
    {
        $this->assertEquals($expected, Amp::isCustomElement($node));
    }

    /**
     * Provide data for the testGetExtensionName() method.
     *
     * @return array[] Array
     */
    public function dataExtensionTests()
    {
        $dom = new Document();

        $customElement = $dom->createElement(Tag::SCRIPT);
        $customElement->setAttribute(Attribute::CUSTOM_ELEMENT, 'amp-custom-element-example');

        $customTemplate = $dom->createElement(Tag::SCRIPT);
        $customTemplate->setAttribute(Attribute::CUSTOM_TEMPLATE, 'amp-custom-template-example');

        return [
            Attribute::CUSTOM_ELEMENT  => [$customElement, 'amp-custom-element-example', true],
            Attribute::CUSTOM_TEMPLATE => [$customTemplate, 'amp-custom-template-example', true],
            'script-without-attribute' => [$dom->createElement(Tag::SCRIPT), '', false],
            'template-tag'             => [$dom->createElement(Tag::TEMPLATE), '', false],
            'non-element'              => [$dom->createTextNode(Attribute::CUSTOM_ELEMENT), '', false],
        ];
    }

    /**
     * Test the retrieval of an extension's name.
     *
     * @dataProvider dataExtensionTests
     * @covers       \AmpProject\Amp::getExtensionName()
     *
     * @param DOMNode $node     Node to get the name of.
     * @param string  $expected Expected string result.
     * @param bool    $_        (unused).
     */
    public function testGetExtensionName(DOMNode $node, $expected, $_)
    {
        $this->assertEquals($expected, Amp::getExtensionName($node));
    }

    /**
     * Test the check whether a node is an extension.
     *
     * @dataProvider dataExtensionTests
     * @covers       \AmpProject\Amp::isExtension()
     *
     * @param DOMNode $node     Node to check.
     * @param string  $_        (unused).
     * @param bool    $expected Expected boolean result.
     */
    public function testIsExtension(DOMNode $node, $_, $expected)
    {
        $this->assertEquals($expected, Amp::isExtension($node));
    }

    /**
     * Provide data for the testIsAmpStory() method.
     *
     * @return array[] Array
     */
    public function dataIsAmpStory()
    {
        $domAmpStoryCustomElement = Document::fromHtml('');
        $ampStoryCustomElement    = $domAmpStoryCustomElement->createElement(Tag::SCRIPT);
        $ampStoryCustomElement->setAttribute(Attribute::CUSTOM_ELEMENT, 'amp-story');
        $domAmpStoryCustomElement->head->appendChild($ampStoryCustomElement);

        $domOtherCustomElement = Document::fromHtml('');
        $otherCustomElement    = $domOtherCustomElement->createElement(Tag::SCRIPT);
        $otherCustomElement->setAttribute(Attribute::CUSTOM_ELEMENT, 'amp-something-else');
        $domOtherCustomElement->head->appendChild($otherCustomElement);

        $domAmpStoryCustomTemplate = Document::fromHtml('');
        $ampStoryCustomTemplate    = $domAmpStoryCustomTemplate->createElement(Tag::SCRIPT);
        $ampStoryCustomTemplate->setAttribute(Attribute::CUSTOM_TEMPLATE, 'amp-story');
        $domAmpStoryCustomTemplate->head->appendChild($ampStoryCustomTemplate);

        return [
            'amp-story-custom-element' => [$domAmpStoryCustomElement, true],
            'other-custom-element'     => [$domOtherCustomElement, false],
            'amp-story-template'       => [$domAmpStoryCustomTemplate, false],
        ];
    }

    /**
     * Test the check for whether a document is an AMP Story.
     *
     * @dataProvider dataIsAmpStory
     * @covers       \AmpProject\Amp::isAmpStory()
     *
     * @param Document $document Document to get check for an AMP Story.
     * @param bool     $expected Expected result.
     */
    public function testIsAmpStory(Document $document, $expected)
    {
        $this->assertEquals($expected, Amp::isAmpStory($document));
    }

    /**
     * Provide data for the testIsTemplate() method.
     *
     * @return array[] Array
     */
    public function dataIsTemplate()
    {
        $dom = new Document();

        $templateScript = $dom->createElement(Tag::SCRIPT);
        $templateScript->setAttribute(Attribute::TEMPLATE, Extension::MUSTACHE);

        return [
            'non-element'         => [$dom->createTextNode('template'), false],
            'template-element'    => [$dom->createElement(Tag::TEMPLATE), true],
            'template-script'     => [$templateScript, true],
            'non-template-script' => [$dom->createElement(Tag::SCRIPT), false],
        ];
    }

    /**
     * Test the check whether a given node is an AMP template.
     *
     * @dataProvider dataIsTemplate
     * @covers       \AmpProject\Amp::isTemplate()
     *
     * @param DOMNode $node     Node to check.
     * @param bool    $expected Expected boolean result.
     */
    public function testIsTemplate(DOMNode $node, $expected)
    {
        $this->assertEquals($expected, Amp::isTemplate($node));
    }

    /**
     * Create an AMP CDN script to a given URL.
     *
     * @param Document $dom DOM document object to use.
     * @param string   $src Source URL to use.
     * @return Element AMP CDN script element
     */
    protected function createAmpCDNScript(Document $dom, $src)
    {
        $runtime = $dom->createElement(Tag::SCRIPT);
        $runtime->setAttribute(Attribute::ASYNC, '');
        $runtime->setAttribute(Attribute::SRC, $src);
        return $runtime;
    }

    /**
     * Create an extension script with a given name.
     *
     * @param Document $dom  DOM document object to use.
     * @param string   $name Name of the extension script to create.
     * @param string   $type Type of the extension script to crate. Defaults to 'custom-element'.
     * @return Element Custom element.
     */
    protected function createExtensionScript(Document $dom, $name, $type = Attribute::CUSTOM_ELEMENT)
    {
        $element = $dom->createElement(Tag::SCRIPT);
        $element->setAttribute($type, $name);
        return $element;
    }

    /**
     * Provide data for the testIsAmpIframe() method.
     *
     * @return array[] Array
     */
    public function dataIsAmpIframe()
    {
        $dom = new Document();

        $templateScript = $dom->createElement(Tag::SCRIPT);
        $templateScript->setAttribute(Attribute::TEMPLATE, Extension::MUSTACHE);

        return [
            'non-element'              => [$dom->createTextNode(Extension::IFRAME), false],
            'amp-iframe-element'       => [$dom->createElement(Extension::IFRAME), true],
            'amp-video-iframe-element' => [$dom->createElement(Extension::VIDEO_IFRAME), true],
            'other-element'            => [$dom->createElement(Extension::VIDEO), false],
        ];
    }

    /**
     * Test the check whether a given node is an AMP iframe.
     *
     * @dataProvider dataIsAmpIframe
     * @covers       \AmpProject\Amp::isAmpIframe()
     *
     * @param DOMNode $node     Node to check.
     * @param bool    $expected Expected boolean result.
     */
    public function testIsAmpIframe(DOMNode $node, $expected)
    {
        $this->assertEquals($expected, Amp::isAmpIframe($node));
    }
}
