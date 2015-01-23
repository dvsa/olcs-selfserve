<?php

namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Mockery as m;

/**
 * Test Undertakings (Declarations) Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UndertakingsControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\UndertakingsController');
    }

    /**
     * Test the logic for determining which undertakings html is shown
     *
     * @dataProvider undertakingsPartialProvider
     */
    public function testGetUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->sut->getUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag)
        );
    }

    public function undertakingsPartialProvider()
    {
        return [
            'GB Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'N', 'markup-undertakings-gv79-standard'],
            'GB Goods Standard International' => ['lcat_gv', 'ltyp_si', 'N', 'markup-undertakings-gv79-standard'],
            'GB Goods Restricted'             => ['lcat_gv', 'ltyp_r', 'N', 'markup-undertakings-gv79-restricted'],
            'NI Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'Y', 'markup-undertakings-gvni79-standard'],
            'NI Goods Standard International' => ['lcat_gv', 'ltyp_si', 'Y', 'markup-undertakings-gvni79-standard'],
            'NI Goods Restricted'             => ['lcat_gv', 'ltyp_r', 'Y', 'markup-undertakings-gvni79-restricted'],
            'PSV Standard National'           => ['lcat_psv', 'ltyp_sn', 'N', 'markup-undertakings-psv421-standard'],
            'PSV Standard International'      => ['lcat_psv', 'ltyp_si', 'N', 'markup-undertakings-psv421-standard'],
            'PSV Restricted'                  => ['lcat_psv', 'ltyp_r', 'N', 'markup-undertakings-psv421-restricted'],
            'PSV Special Restricted'          => ['lcat_psv', 'ltyp_sr', 'N', 'markup-undertakings-psv356'],
        ];
    }

    /**
     * Test edge case logic for determining which undertakings html is shown
     *
     * @dataProvider partialInvalidProvider
     */
    public function testGetUndertakingsPartialInvalid($goodsOrPsv, $typeOfLicence, $niFlag, $expectedMessage)
    {
        $this->setExpectedException('\LogicException', $expectedMessage);
        $this->sut->getUndertakingsPartial($goodsOrPsv, $typeOfLicence, $niFlag);
    }

    public function partialInvalidProvider()
    {
        return [
            'invalid goods licence type'      => ['lcat_gv', 'foo', 'N', 'Licence Type not set or invalid'],
            'invalid goods licence type (SR)' => ['lcat_gv', 'ltyp_sr', 'N', 'Licence Type not set or invalid'],
            'invalid psv licence type'        => ['lcat_psv', 'foo', 'N', 'Licence Type not set or invalid'],
            'invalid licence category'        => ['foo', 'ltyp_sr', 'N', 'Licence Category not set or invalid'],
        ];
    }

    /**
     * Test the logic for determining which declarations html is shown
     *
     * @dataProvider declarationsPartialProvider
     */
    public function testGetDeclarationsPartial($goodsOrPsv, $typeOfLicence, $niFlag, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->sut->getDeclarationsPartial($goodsOrPsv, $typeOfLicence, $niFlag)
        );
    }

    public function declarationsPartialProvider()
    {
        return [
            'GB Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'N', 'markup-declarations-gv79'],
            'GB Goods Standard International' => ['lcat_gv', 'ltyp_si', 'N', 'markup-declarations-gv79'],
            'GB Goods Restricted'             => ['lcat_gv', 'ltyp_r', 'N', 'markup-declarations-gv79'],
            'NI Goods Standard National'      => ['lcat_gv', 'ltyp_sn', 'Y', 'markup-declarations-gv79'],
            'NI Goods Standard International' => ['lcat_gv', 'ltyp_si', 'Y', 'markup-declarations-gv79'],
            'NI Goods Restricted'             => ['lcat_gv', 'ltyp_r', 'Y', 'markup-declarations-gv79'],
            'PSV Standard National'           => ['lcat_psv', 'ltyp_sn', 'N', 'markup-declarations-psv421'],
            'PSV Standard International'      => ['lcat_psv', 'ltyp_si', 'N', 'markup-declarations-psv421'],
            'PSV Restricted'                  => ['lcat_psv', 'ltyp_r', 'N', 'markup-declarations-psv421'],
            'PSV Special Restricted'          => ['lcat_psv', 'ltyp_sr', 'N', 'markup-declarations-psv356'],
        ];
    }

    /**
     * Test edge case logic for determining which declarations html is shown
     *
     * @dataProvider partialInvalidProvider
     */
    public function testGetDeclarationsPartialInvalid($goodsOrPsv, $typeOfLicence, $niFlag, $expectedMessage)
    {
        $this->setExpectedException('\LogicException', $expectedMessage);
        $this->sut->getDeclarationsPartial($goodsOrPsv, $typeOfLicence, $niFlag);
    }

    /**
     * @dataProvider undertakingsPartialProvider
     * @dataProvider declarationsPartialProvider
     */
    public function testUndertakingsPartialExists($g, $t, $n, $partial)
    {

        $this->markTestSkipped('use in dev only!');

        $path = __DIR__ . '/../../../../../../vendor/olcs/OlcsCommon/Common/config/language/partials/';
        $gbFile = $path.'en_GB/'.$partial.'.phtml';
        $cyFile = $path.'cy_GB/'.$partial.'.phtml';
        $this->assertTrue(file_exists($gbFile), "$gbFile not found");
        $this->assertTrue(file_exists($cyFile), "$cyFile not found");
    }
}
