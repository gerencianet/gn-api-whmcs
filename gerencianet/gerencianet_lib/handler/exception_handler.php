<?php

/**
 * Show Exceptions
 * 
 * @param string $type Exception type
 * @param array $exceptions Array of exceptions to show
 */
function showException($type, $exceptions) {
    $errorPage = new \WHMCS\View\HtmlErrorPage();

    // Append error to template body
    foreach($exceptions as $exception) {
        $errorPage->body .= "<li style='padding: 10px 0px;'><b>$type: </b>$exception</li>";
    }

    $html = $errorPage->getHtmlErrorPage();

    die($html);
}