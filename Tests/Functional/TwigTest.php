<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\SettingsBundle\Tests\Functional;

use Trinity\Bundle\SettingsBundle\Manager\SettingsManager;


/**
 * Class TwigTest
 * @package Trinity\Bundle\SettingsBundle\Tests\Functional
 */
class TwigTest extends WebTestCase
{

    function testTwigFunction(){

        /** @var \Twig_Environment $twig */
        $twig = $this->get('twig');
        $twig->setLoader(new \Twig_Loader_Filesystem(__DIR__ . '/template'));

        $template = $twig->loadTemplate('index.html.twig');

        /** @var SettingsManager $settings */
        $settings = $this->get('trinity.settings');
        $settings->set('twíg_variable', 'value');

        $output = $template->render([]);
        $this->assertEquals('Twig text: value', $output);
    }

}