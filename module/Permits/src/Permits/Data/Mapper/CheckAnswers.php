<?php

namespace Permits\Data\Mapper;

use Common\Util\Escape;
use Permits\View\Helper\EcmtSection;

/**
 *
 * Check Answers mapper
 */
class CheckAnswers
{
    public static function mapForDisplay(array $data)
    {
        $questions = [
            'permits.check-answers.page.question.licence',
            'permits.form.euro-emissions.label',
            'permits.form.cabotage.label',
            'permits.page.restricted-countries.question',
            'permits.page.permits.required.question',
            'permits.page.number-of-trips.question',
            'permits.page.international.journey.question',
            'permits.page.sectors.question'
        ];

        $routes = [
            EcmtSection::ROUTE_LICENCE,
            EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
            EcmtSection::ROUTE_ECMT_CABOTAGE,
            EcmtSection::ROUTE_ECMT_COUNTRIES,
            EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
            EcmtSection::ROUTE_ECMT_TRIPS,
            EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
            EcmtSection::ROUTE_ECMT_SECTORS
        ];

        $restrictedCountries = 'No';

        if ($data['application']['hasRestrictedCountries']) {
            $countries = [];

            foreach ($data['application']['countrys'] as $country) {
                $countries[] = $country['countryDesc'];
            }

            $restrictedCountries = ['Yes', implode(', ', $countries)];
        }

        $answersFormatted = [
            [
                Escape::html($data['application']['licence']['licNo']),
                Escape::html($data['application']['licence']['trafficArea']['name']),
            ],
            $data['application']['emissions'] ? 'Yes' : 'No',
            $data['application']['cabotage'] ? 'Yes' : 'No',
            $restrictedCountries,
            $data['application']['permitsRequired'],
            $data['application']['trips'],
            $data['application']['internationalJourneys']['description'],
            $data['application']['sectors']['name']
        ];

        foreach ($questions as $index => $question) {
            $answers[] = [
                'question' => $question,
                'route' => $routes[$index],
                'answer' => $answersFormatted[$index]
            ];
        }

        return [
            'canCheckAnswers' => $data['application']['canCheckAnswers'],
            'answers' => $answers,
            'applicationRef' => $data['application']['applicationRef']
        ];
    }
}
