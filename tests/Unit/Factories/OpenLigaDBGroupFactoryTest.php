<?php

namespace Rockschtar\Soccr\Tests\Unit\Factories;

use Rockschtar\Soccr\Factories\OpenLigaDBGroupFactory;
use Rockschtar\Soccr\Tests\Unit\UnitTestCase;

class OpenLigaDBGroupFactoryTest extends UnitTestCase
{
    public function test_creates_group_from_json(): void
    {
        $json = (object) [
            'groupName'    => 'Spieltag 1',
            'groupOrderID' => '1',
            'groupID'      => '42',
        ];

        $group = OpenLigaDBGroupFactory::createFromJSON($json);

        $this->assertSame('Spieltag 1', $group->getGroupName());
        $this->assertSame(1, $group->getGroupOrderId());
        $this->assertSame('42', $group->getGroupId());
    }
}
