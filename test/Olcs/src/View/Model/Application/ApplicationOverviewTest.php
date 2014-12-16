<?php

/**
 * Application Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\View\Model\Application;

use Olcs\View\Model\Application\ApplicationOverview;

/**
 * Application Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationOverviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constructor with set variables
     * 
     * @group applicationOverview
     */
    public function testSetVariables()
    {
        $data = [
            'id' => 1,
            'createdOn' => '2014-01-01',
            'status' => ['id' => 'status'],
            'submissionForm' => 'form',
            'receivedDate' => '2014-01-01',
            'targetCompletionDate' => '2014-01-01'
        ];
        $overview = new ApplicationOverview($data);
        $this->assertEquals($overview->applicationId, 1);
        $this->assertEquals($overview->createdOn, '01 January 2014');
        $this->assertEquals($overview->status, 'status');
        $this->assertEquals($overview->receivedDate, '2014-01-01');
        $this->assertEquals($overview->completionDate, '2014-01-01');
    }
}
