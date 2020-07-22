<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
use Permits\Data\Mapper\AvailableStocks;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use RuntimeException;

/**
 * AvailableStocksTest
 */
class AvailableStocksTest extends TestCase
{
    private $sut;

    public function setUp(): void
    {
        $this->sut = new AvailableStocks();
    }

    /**
     * @dataProvider dpTestExceptionNotSupported
     */
    public function testExceptionNotSupported($typeId)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This mapper does not support permit type ' . $typeId);

        $data = [
            'type' => $typeId
        ];

        $this->sut->mapForFormOptions(
            $data,
            m::mock(Form::class)
        );
    }

    public function dpTestExceptionNotSupported()
    {
        return [
            [RefData::ECMT_PERMIT_TYPE_ID],
            [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID],
            [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID],
            [RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID],
        ];
    }

    public function testEcmtShortTermSingleOption()
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                ]
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => 1,
                'label' => 'period.name.key.1',
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'attributes' => [
                    'id' => 'stock'
                ]
            ]
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('stock')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        $expectedData = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                ]
            ],
            'guidance' => [
                'value' => 'permits.page.stock.guidance.one-available',
                'disableHtmlEscape' => true,
            ],
        ];

        $returnedData = $this->sut->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }

    public function testEcmtShortTermMultipleOptions()
    {
        $data = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                    [
                        'id' => 2,
                        'periodNameKey' => 'period.name.key.2',
                    ],
                ]
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => 1,
                'label' => 'period.name.key.1',
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'attributes' => [
                    'id' => 'stock'
                ]
            ],
            [
                'value' => 2,
                'label' => 'period.name.key.2',
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
            ],
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('stock')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        $expectedData = [
            'type' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
            'stocks' => [
                'stocks' => [
                    [
                        'id' => 1,
                        'periodNameKey' => 'period.name.key.1',
                    ],
                    [
                        'id' => 2,
                        'periodNameKey' => 'period.name.key.2',
                    ],
                ]
            ],
            'guidance' => [
                'value' => 'permits.page.stock.guidance.multiple-available',
                'disableHtmlEscape' => true,
            ],
        ];

        $returnedData = $this->sut->mapForFormOptions($data, $form);

        $this->assertEquals($expectedData, $returnedData);
    }
}