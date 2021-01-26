<?php

namespace AmpProject\Optimizer;

use AmpProject\Tests\TestCase;

/**
 * Tests for AmpProject\Optimizer\CssRules.
 *
 * @covers  \AmpProject\Optimizer\CssRules
 * @package ampproject/amp-toolbox
 */
class CssRulesTest extends TestCase
{

    /**
     * @covers \AmpProject\Optimizer\CssRules::getCss()
     */
    public function testEmptyCollection()
    {
        $cssRules = new CssRules();
        $this->assertEquals('', $cssRules->getCss());
    }

    /**
     * @covers \AmpProject\Optimizer\CssRules::add()
     * @covers \AmpProject\Optimizer\CssRules::getCss()
     */
    public function testSingleRule()
    {
        $cssRules = (new CssRules())
            ->add(new CssRule('h1', 'color:red'));
        $this->assertEquals('h1{color:red}', $cssRules->getCss());
    }

    /**
     * @covers \AmpProject\Optimizer\CssRules::add()
     * @covers \AmpProject\Optimizer\CssRules::getCss()
     */
    public function testMultipleRules()
    {
        $cssRules = (new CssRules())
            ->add(new CssRule('h1', 'color:red'))
            ->add(new CssRule('h2', 'color:green'))
            ->add(new CssRule('h3', 'color:blue'));
        $this->assertEquals('h1{color:red}h2{color:green}h3{color:blue}', $cssRules->getCss());
    }

    /**
     * @covers \AmpProject\Optimizer\CssRules::add()
     * @covers \AmpProject\Optimizer\CssRules::getCss()
     */
    public function testMultipleRulesWithOverlap()
    {
        $cssRules = (new CssRules())
            ->add(new CssRule('h1', 'color:red'))
            ->add(new CssRule('h2', 'color:green'))
            ->add(new CssRule('h3', 'color:red'))
            ->add(new CssRule('h4', 'color:green'))
            ->add(new CssRule('h5', 'color:red'));
        $this->assertEquals('h1,h3,h5{color:red}h2,h4{color:green}', $cssRules->getCss());
    }

    /**
     * @covers \AmpProject\Optimizer\CssRules::add()
     * @covers \AmpProject\Optimizer\CssRules::getCss()
     */
    public function testNamedConstructor()
    {
        $cssRules = CssRules::fromCssRuleArray(
            [
                new CssRule('h1', 'color:red'),
                new CssRule('h2', 'color:green'),
                new CssRule('h3', 'color:red'),
                new CssRule('h4', 'color:green'),
                new CssRule('h5', 'color:red'),
            ]
        );
        $this->assertEquals('h1,h3,h5{color:red}h2,h4{color:green}', $cssRules->getCss());
    }
}
