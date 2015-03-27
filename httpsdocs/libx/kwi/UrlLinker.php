<?php

namespace Kwi;

class UrlLinker implements UrlLinkerInterface
{
    /**
     * @var UrlLinker|null
     */
    private static $instance;

    /**
     * Associative array mapping valid TLDs to the value true.
     *
     * @var string
     */
    private static $validTlds;

    /**
     * @var string
     */
    private $rexUrlLinker;

    /**
     * @return UrlLinker
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param bool $allowFtpAddresses
     * @param bool $allowUpperCaseUrlSchemes e.g. "HTTP://google.com"
     */
    public function __construct($allowFtpAddresses = false, $allowUpperCaseUrlSchemes = false)
    {
        /**
         * Regular expression bits used by linkUrlsAndEscapeHtml() to match URLs.
         */
        $rexScheme = 'https?://';

        if ($allowFtpAddresses) {
            $rexScheme .= '|ftp://';
        }

        $rexDomain     = '(?:[-a-zA-Z0-9\x7f-\xff]{1,63}\.)+[a-zA-Z\x7f-\xff][-a-zA-Z0-9\x7f-\xff]{1,62}';
        $rexIp         = '(?:[1-9][0-9]{0,2}\.|0\.){3}(?:[1-9][0-9]{0,2}|0)';
        $rexPort       = '(:[0-9]{1,5})?';
        $rexPath       = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
        $rexQuery      = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
        $rexFragment   = '(#[!$-/0-9?:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
        $rexUsername   = '[^]\\\\\x00-\x20\"(),:-<>[\x7f-\xff]{1,64}';
        $rexPassword   = $rexUsername; // allow the same characters as in the username
        $rexUrl        = "($rexScheme)?(?:($rexUsername)(:$rexPassword)?@)?($rexDomain|$rexIp)($rexPort$rexPath$rexQuery$rexFragment)";
        $rexTrailPunct = "[)'?.!,;:]"; // valid URL characters which are not part of the URL if they appear at the very end
        $rexNonUrl     = "[^-_#$+.!*%'(),;/?:@=&a-zA-Z0-9\x7f-\xff]"; // characters that should never appear in a URL

        $this->rexUrlLinker = "{\\b$rexUrl(?=$rexTrailPunct*($rexNonUrl|$))}";

        if ($allowUpperCaseUrlSchemes) {
            $this->rexUrlLinker .= 'i';
        }

        if (!static::$validTlds) {
            static::$validTlds = array_fill_keys(explode(' ', require __DIR__.'/validTlds.php'), true);
        }
    }

    /**
     * Transforms plain text into valid HTML, escaping special characters and
     * turning URLs into links.
     *
     * @param string $text
     * @return string
     */
    public function linkUrlsAndEscapeHtml($text)
    {
        $html = '';

        $position = 0;

        $match = array();

        while (preg_match($this->rexUrlLinker, $text, $match, PREG_OFFSET_CAPTURE, $position)) {
            list($url, $urlPosition) = $match[0];

            // Add the text leading up to the URL.
            $html .= $this->escapeHtml(substr($text, $position, $urlPosition - $position));

            $scheme      = $match[1][0];
            $username    = $match[2][0];
            $password    = $match[3][0];
            $domain      = $match[4][0];
            $afterDomain = $match[5][0]; // everything following the domain
            $port        = $match[6][0];
            $path        = $match[7][0];

            // Check that the TLD is valid or that $domain is an IP address.
            $tld = strtolower(strrchr($domain, '.'));

            if (preg_match('{^\.[0-9]{1,3}$}', $tld) || isset(static::$validTlds[$tld])) {
                // Do not permit implicit scheme if a password is specified, as
                // this causes too many errors (e.g. "my email:foo@example.org").
                if (!$scheme && $password) {
                    $html .= $this->escapeHtml($username);

                    // Continue text parsing at the ':' following the "username".
                    $position = $urlPosition + strlen($username);

                    continue;
                }

                if (!$scheme && $username && !$password && !$afterDomain) {
                    // Looks like an email address.
                    $completeUrl = "mailto:$url";
                    $linkText = $url;
                } else {
                    // Prepend http:// if no scheme is specified
                    $completeUrl = $scheme ? $url : "http://$url";
                    $linkText = "$domain$port$path";
                }

                // Add the hyperlink.
                $html .= $this->createHtmlLink($completeUrl, $linkText);
            } else {
                // Not a valid URL.
                $html .= $this->escapeHtml($url);
            }

            // Continue text parsing from after the URL.
            $position = $urlPosition + strlen($url);
        }

        // Add the remainder of the text.
        $html .= $this->escapeHtml(substr($text, $position));

        return $html;
    }

    /**
     * Turns URLs into links in a piece of valid HTML/XHTML.
     *
     * Beware: Never render HTML from untrusted sources. Rendering HTML provided by
     * a malicious user can lead to system compromise through cross-site scripting.
     *
     * @param string $html
     * @return string
     */
    public function linkUrlsInTrustedHtml($html)
    {
        $reMarkup = '{</?([a-z]+)([^"\'>]|"[^"]*"|\'[^\']*\')*>|&#?[a-zA-Z0-9]+;|$}';

        $insideAnchorTag = false;
        $position = 0;
        $result = '';

        // Iterate over every piece of markup in the HTML.
        while (true) {
            $match = array();
            preg_match($reMarkup, $html, $match, PREG_OFFSET_CAPTURE, $position);

            list($markup, $markupPosition) = $match[0];

            // Process text leading up to the markup.
            $text = substr($html, $position, $markupPosition - $position);

            // Link URLs unless we're inside an anchor tag.
            if (!$insideAnchorTag) {
                $text = $this->linkUrlsAndEscapeHtml($text);
            }

            $result .= $text;

            // End of HTML?
            if ($markup === '') {
                break;
            }

            // Check if markup is an anchor tag ('<a>', '</a>').
            if ($markup[0] !== '&' && $match[1][0] === 'a') {
                $insideAnchorTag = ($markup[1] !== '/');
            }

            // Pass markup through unchanged.
            $result .= $markup;

            // Continue after the markup.
            $position = $markupPosition + strlen($markup);
        }

        return $result;
    }

    /**
     * @param string $url
     * @param string $content
     * @return string
     */
    private function createHtmlLink($url, $content)
    {
        $link = sprintf(
            '<a href="%s">%s</a>',
            $this->escapeHtml($url),
            $this->escapeHtml($content)
        );

        // Cheap e-mail obfuscation to trick the dumbest mail harvesters.
        return str_replace('@', '&#64;', $link);
    }

    /**
     * @param string $string
     * @return string
     */
    private function escapeHtml($string)
    {
        return htmlspecialchars($string);
    }
}
