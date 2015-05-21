<?php

namespace Box\Component\Builder\Tests\Event\Listener;

use Box\Component\Builder\Event\Listener\RegexBlacklistSubscriber;
use Box\Component\Builder\Tests\AbstractBuilderTestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class RegexBlacklistSubscriberTest extends AbstractBuilderTestCase
{
    /**
     * Verifies that blacklist files and directories.
     *
     * @covers \Box\Component\Builder\Event\Listener\AbstractListFilterSubscriber
     * @covers \Box\Component\Builder\Event\Listener\RegexBlacklistSubscriber
     */
    public function testIsAllowed()
    {
        // by default everything is allowed
        $subscriber = new RegexBlacklistSubscriber(array(), array());

        self::assertTrue($subscriber->isAllowed('', false));
        self::assertTrue($subscriber->isAllowed('', true));

        // make sure with can filter only directories
        $subscriber = new RegexBlacklistSubscriber(
            array(),
            array('/test/')
        );

        self::assertTrue($subscriber->isAllowed('test', false));
        self::assertFalse($subscriber->isAllowed('test', true));
        self::assertTrue($subscriber->isAllowed('another', true));

        // make sure with can filter only files
        $subscriber = new RegexBlacklistSubscriber(
            array('/test/'),
            array()
        );

        self::assertTrue($subscriber->isAllowed('test', true));
        self::assertFalse($subscriber->isAllowed('test', false));
        self::assertTrue($subscriber->isAllowed('another', false));
    }
}
