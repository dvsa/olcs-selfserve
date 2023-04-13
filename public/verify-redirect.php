<?php
declare(strict_types=1);

$allowedReferer = 'signin.service.gov.uk';
$referer = $_SERVER['HTTP_REFERER'];
if (empty($referer)) {
    header('Location: /', true, 302);
    exit;
}

$refererHost =  parse_url($referer,PHP_URL_HOST);
$trimmedHost = implode('.', array_slice(explode('.', $refererHost), -4, 4));

if ($allowedReferer !== $trimmedHost) {
    header('Location: /', true, 302);
    exit;
}

$samlResponse = urlencode($_POST['SAMLResponse'] ?? $_GET['SAMLResponse'] ?? '');

?>
<!DOCTYPE html>
<html class="govuk-template app-html-class checked" lang="en">

<head>
    <meta charset="utf-8">
    <title>Vehicle Operator Licensing - GOV.UK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="blue">
    <link href="/static/public/styles/selfserve.css" media="screen,print"
          rel="stylesheet" type="text/css">

    <link rel="shortcut icon" sizes="16x16 32x32 48x48" href="/static/public/assets/images/favicon.ico"
          type="image/x-icon">
    <link rel="mask-icon" href="/static/public/assets/images/govuk-mask-icon.svg"
          color="blue">
    <link rel="apple-touch-icon" sizes="180x180" href="/static/public/assets/images/govuk-apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/static/public/assets/images/govuk-apple-touch-icon-167x167.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/static/public/assets/images/govuk-apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" href="/static/public/assets/images/govuk-apple-touch-icon.png">
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <meta property="og:image" content="/static/public/assets/images/govuk-opengraph-image.png">
</head>

<body class="govuk-template__body app-body-class">


<a href="#main-content" class="govuk-skip-link" data-module="govuk-skip-link">Skip to main content</a>

<header class="govuk-header" role="banner" data-module="govuk-header">
    <div class="govuk-header__container govuk-width-container">
        <div class="govuk-header__logo">
            <a href="" title="Go to dashboard" class="govuk-header__link govuk-header__link--homepage">
                    <span class="govuk-header__logotype">
                        <svg aria-hidden="true" focusable="false" class="govuk-header__logotype-crown"
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 132 97" height="30" width="36">
                            <path fill="currentColor" fill-rule="evenodd"
                                  d="M25 30.2c3.5 1.5 7.7-.2 9.1-3.7 1.5-3.6-.2-7.8-3.9-9.2-3.6-1.4-7.6.3-9.1 3.9-1.4 3.5.3 7.5 3.9 9zM9 39.5c3.6 1.5 7.8-.2 9.2-3.7 1.5-3.6-.2-7.8-3.9-9.1-3.6-1.5-7.6.2-9.1 3.8-1.4 3.5.3 7.5 3.8 9zM4.4 57.2c3.5 1.5 7.7-.2 9.1-3.8 1.5-3.6-.2-7.7-3.9-9.1-3.5-1.5-7.6.3-9.1 3.8-1.4 3.5.3 7.6 3.9 9.1zm38.3-21.4c3.5 1.5 7.7-.2 9.1-3.8 1.5-3.6-.2-7.7-3.9-9.1-3.6-1.5-7.6.3-9.1 3.8-1.3 3.6.4 7.7 3.9 9.1zm64.4-5.6c-3.6 1.5-7.8-.2-9.1-3.7-1.5-3.6.2-7.8 3.8-9.2 3.6-1.4 7.7.3 9.2 3.9 1.3 3.5-.4 7.5-3.9 9zm15.9 9.3c-3.6 1.5-7.7-.2-9.1-3.7-1.5-3.6.2-7.8 3.7-9.1 3.6-1.5 7.7.2 9.2 3.8 1.5 3.5-.3 7.5-3.8 9zm4.7 17.7c-3.6 1.5-7.8-.2-9.2-3.8-1.5-3.6.2-7.7 3.9-9.1 3.6-1.5 7.7.3 9.2 3.8 1.3 3.5-.4 7.6-3.9 9.1zM89.3 35.8c-3.6 1.5-7.8-.2-9.2-3.8-1.4-3.6.2-7.7 3.9-9.1 3.6-1.5 7.7.3 9.2 3.8 1.4 3.6-.3 7.7-3.9 9.1zM69.7 17.7l8.9 4.7V9.3l-8.9 2.8c-.2-.3-.5-.6-.9-.9L72.4 0H59.6l3.5 11.2c-.3.3-.6.5-.9.9l-8.8-2.8v13.1l8.8-4.7c.3.3.6.7.9.9l-5 15.4v.1c-.2.8-.4 1.6-.4 2.4 0 4.1 3.1 7.5 7 8.1h.2c.3 0 .7.1 1 .1.4 0 .7 0 1-.1h.2c4-.6 7.1-4.1 7.1-8.1 0-.8-.1-1.7-.4-2.4V34l-5.1-15.4c.4-.2.7-.6 1-.9zM66 92.8c16.9 0 32.8 1.1 47.1 3.2 4-16.9 8.9-26.7 14-33.5l-9.6-3.4c1 4.9 1.1 7.2 0 10.2-1.5-1.4-3-4.3-4.2-8.7L108.6 76c2.8-2 5-3.2 7.5-3.3-4.4 9.4-10 11.9-13.6 11.2-4.3-.8-6.3-4.6-5.6-7.9 1-4.7 5.7-5.9 8-.5 4.3-8.7-3-11.4-7.6-8.8 7.1-7.2 7.9-13.5 2.1-21.1-8 6.1-8.1 12.3-4.5 20.8-4.7-5.4-12.1-2.5-9.5 6.2 3.4-5.2 7.9-2 7.2 3.1-.6 4.3-6.4 7.8-13.5 7.2-10.3-.9-10.9-8-11.2-13.8 2.5-.5 7.1 1.8 11 7.3L80.2 60c-4.1 4.4-8 5.3-12.3 5.4 1.4-4.4 8-11.6 8-11.6H55.5s6.4 7.2 7.9 11.6c-4.2-.1-8-1-12.3-5.4l1.4 16.4c3.9-5.5 8.5-7.7 10.9-7.3-.3 5.8-.9 12.8-11.1 13.8-7.2.6-12.9-2.9-13.5-7.2-.7-5 3.8-8.3 7.1-3.1 2.7-8.7-4.6-11.6-9.4-6.2 3.7-8.5 3.6-14.7-4.6-20.8-5.8 7.6-5 13.9 2.2 21.1-4.7-2.6-11.9.1-7.7 8.8 2.3-5.5 7.1-4.2 8.1.5.7 3.3-1.3 7.1-5.7 7.9-3.5.7-9-1.8-13.5-11.2 2.5.1 4.7 1.3 7.5 3.3l-4.7-15.4c-1.2 4.4-2.7 7.2-4.3 8.7-1.1-3-.9-5.3 0-10.2l-9.5 3.4c5 6.9 9.9 16.7 14 33.5 14.8-2.1 30.8-3.2 47.7-3.2z">
                            </path>
                            <image src="/static/public/assets/images/govuk-logotype-crown.png" xlink:href=""
                                   class="govuk-header__logotype-crown-fallback-image" width="36" height="32"></image>
                        </svg>
                        <span class="govuk-header__logotype-text">
                            GOV.UK
                        </span>
                    </span>
            </a>
        </div>
        <div class="govuk-header__content">
            <a href="" class="govuk-header__link govuk-header__service-name">
                Vehicle Operator Licensing </a>
            <button type="button" class="govuk-header__menu-button govuk-js-header-toggle"
                    aria-controls="navigation" aria-label="Show or hide Top Level Navigation">
                Menu
            </button>
        </div>
    </div>
</header>


<div class="govuk-width-container app-width-container">
    <div class="govuk-phase-banner">
        <p class="govuk-phase-banner__content">
            <strong class="govuk-tag govuk-phase-banner__content__tag">
                beta
            </strong>
        </p>
    </div>

    <main class="govuk-main-wrapper app-main-class" id="main-content" role="main">
        <form method="post" name="VerifyResponse" novalidate="novalidate" id="verify-redirect"
              action="/verify/process-response">
            <div class="field govuk-visually-hidden">
                <button type="submit" name="form-actions[continue]"
                        class="govuk-visually-hidden" style="display: none;" id="hidden-continue" value="">Continue
                </button>
            </div>
            <fieldset data-group="details">
                <div class="field ">You should automatically be redirected.</div>
            </fieldset>
            <fieldset class="actions-container" data-group="formActions">
                <button type="submit"
                        name="formActions[submit]" class="action--primary large" id="formActions[submit]"
                        value="">Continue
                </button>
            </fieldset>
            <input type="hidden" name="SAMLResponse" id="SAMLResponse"
                   value="<?= $samlResponse; ?>">
        </form>
    </main>
</div>

<footer class="govuk-footer" role="contentinfo">
    <div class="govuk-width-container ">
        <div class="govuk-footer__meta">
            <div class="govuk-footer__meta-item govuk-footer__meta-item--grow">
                <h2 class="govuk-visually-hidden">Support links</h2>
                <ul class="govuk-footer__inline-list">
                    <li class="govuk-footer__support">
                        Telephone: <a class="govuk-footer__link" href="tel:02045518711">020 4551 8711</a><br>
                        Email: <a class="govuk-footer__link"
                                  href="mailto:notifications@vehicle-operator-licensing.service.gov.uk">notifications@vehicle-operator-licensing.service.gov.uk</a>
                    </li>
                    <li class="govuk-footer__inline-list-item">
                        <a class="govuk-footer__link" href="/terms-and-conditions/">
                            Terms and conditions </a>
                    </li>
                    <li class="govuk-footer__inline-list-item">
                        <a class="govuk-footer__link" href="/cookies/settings/">
                            Cookies </a>
                    </li>
                    <li class="govuk-footer__inline-list-item">
                        <a class="govuk-footer__link" href="/privacy-notice/">
                            Privacy notice </a>
                    </li>
                    <li class="govuk-footer__inline-list-item">
                        <a class="govuk-footer__link" href="/accessibility-statement/">
                            Accessibility statement </a>
                    </li>
                    <li class="govuk-footer__inline-list-item">
                        <a class="govuk-footer__link" href="?lang=cy">Cymraeg</a>
                    </li>
                    <li class="govuk-footer__inline-list-item">
                        Built by <a class="govuk-footer__link"
                                    href="https://www.gov.uk/government/organisations/driver-and-vehicle-standards-agency">
                            Driver and Vehicle Standards Agency </a>
                    </li>
                </ul>
                <svg aria-hidden="true" focusable="false" class="govuk-footer__licence-logo"
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 483.2 195.7" height="17" width="41">
                    <path fill="currentColor"
                          d="M421.5 142.8V.1l-50.7 32.3v161.1h112.4v-50.7zm-122.3-9.6A47.12 47.12 0 0 1 221 97.8c0-26 21.1-47.1 47.1-47.1 16.7 0 31.4 8.7 39.7 21.8l42.7-27.2A97.63 97.63 0 0 0 268.1 0c-36.5 0-68.3 20.1-85.1 49.7A98 98 0 0 0 97.8 0C43.9 0 0 43.9 0 97.8s43.9 97.8 97.8 97.8c36.5 0 68.3-20.1 85.1-49.7a97.76 97.76 0 0 0 149.6 25.4l19.4 22.2h3v-87.8h-80l24.3 27.5zM97.8 145c-26 0-47.1-21.1-47.1-47.1s21.1-47.1 47.1-47.1 47.2 21 47.2 47S123.8 145 97.8 145">
                    </path>
                </svg>
                <span class="govuk-footer__licence-description">
                        All content is available under the <a class="govuk-footer__link"
                                                              href="https://www.gov.uk/government/organisations/driver-and-vehicle-standards-agency"
                                                              rel="license">Open Government Licence v3.0</a>, except where otherwise stated </span>
            </div>
            <div class="govuk-footer__meta-item">
                <a class="govuk-footer__link govuk-footer__copyright-logo"
                   href="http://www.nationalarchives.gov.uk/information-management/re-using-public-sector-information/uk-government-licensing-framework/crown-copyright/">©
                    Crown copyright</a>
            </div>
        </div>
    </div>
</footer>

<script>
    window.onload = function () {
        window.setTimeout(function () {
            document.VerifyResponse.submit();
        }, 2000);
    };
</script>
</body>

</html>



