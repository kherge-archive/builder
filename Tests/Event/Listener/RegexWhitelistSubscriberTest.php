<?php

namespace Box\Component\Builder\Tests\Event\Listener;

use Box\Component\Builder\Event\Listener\RegexWhitelistSubscriber;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class RegexWhitelistSubscriberTest extends AbstractBuilderTestCase
{
    /**
     * Verifies that whitelist files and directories.
     *
     * @covers \Box\Component\Builder\Event\Listener\AbstractListFilterSubscriber
     * @covers \Box\Component\Builder\Event\Listener\RegexWhitelistSubscriber
     */
    public function testIsAllowed()
    {
        // by default everything is allowed
        $subscriber = new RegexWhitelistSubscriber(array(), array());

        self::assertTrue($subscriber->isAllowed('', false));
        self::assertTrue($subscriber->isAllowed('', true));

        // make sure with can filter only directories
        $subscriber = new RegexWhitelistSubscriber(
            array(),
            array('/test/')
        );

        self::assertTrue($subscriber->isAllowed('test', false));
        self::assertTrue($subscriber->isAllowed('test', true));
        self::assertFalse($subscriber->isAllowed('another', true));

        // make sure with can filter only files
        $subscriber = new RegexWhitelistSubscriber(
            array('/test/'),
            array()
        );

        self::assertTrue($subscriber->isAllowed('test', true));
        self::assertTrue($subscriber->isAllowed('test', false));
        self::assertFalse($subscriber->isAllowed('another', false));
    }
}
