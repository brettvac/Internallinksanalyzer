<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_internallinksanalyzer
 * @version     1.0
 * @license     GNU General Public License version 2
 */

namespace Naftee\Component\Internallinksanalyzer\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class InternallinksanalyzerModel extends BaseDatabaseModel
{
    /**
     * Cache of menu routes for each article ID.
     *
     * @var array<int, array>
     */
    private array $articleMenuRoutesCache = [];

    /**
     * Retrieve published, publicly accessible articles.
     *
     * @return array
     */
    public function getArticles(): array
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('title'),
                $db->quoteName('alias'),
                $db->quoteName('catid'),
                $db->quoteName('introtext'),
                $db->quoteName('fulltext'),
            ])
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('access') . ' = 1')
            ->order($db->quoteName('title') . ' ASC');

        return $db->setQuery($query)->loadObjectList();
    }

    /**
     * Retrieve a single article by ID.
     *
     * @param   int  $articleId  The article ID.
     *
     * @return  object|null
     */
    public function getArticle(int $articleId): ?object
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('title'),
                $db->quoteName('alias'),
                $db->quoteName('catid'),
                $db->quoteName('introtext'),
                $db->quoteName('fulltext'),
            ])
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('id') . ' = ' . (int) $articleId);

        $article = $db->setQuery($query)->loadObject();

        return $article ?: null;
    }

    /**
     * Find published articles containing links to the target article.
     *
     * Each result includes the source article and its matching links.
     *
     * @param   int  $articleId  The target article ID.
     *
     * @return  array
     */
    public function findIncomingLinks(int $articleId): array
    {
        $targetArticle = $this->getArticle($articleId);

        if (!$targetArticle) {
            return [];
        }

        $targetRoutes = $this->getArticleMenuRoutes($articleId);
        $articles = $this->getArticles();
        $incomingLinks = [];

        foreach ($articles as $sourceArticle) {
            if ((int) $sourceArticle->id === $articleId) {
                continue;
            }

            $content = (string) $sourceArticle->introtext
                . "\n"
                . (string) $sourceArticle->fulltext;

            if (trim($content) === '') {
                continue;
            }

            $links = $this->extractLinks($content);

            foreach ($links as $link) {
                if (
                    $this->linkPointsToArticle(
                        $link,
                        $targetArticle,
                        $targetRoutes
                    )
                ) {
                    $incomingLinks[] = [
                        'article_id'    => (int) $sourceArticle->id,
                        'article_title' => $sourceArticle->title,
                        'article_alias' => $sourceArticle->alias,
                        'link_url'      => $link['href'],
                        'link_text'     => $link['text'],
                        'context'       => $link['context'],
                    ];
                }
            }
        }

        return $incomingLinks;
    }

    /**
     * Find published site menu items that directly target an article.
     *
     * Uses the site menu table rather than relying on the administrator application's router to generate frontend URLs.
     *
     * The menu item's path column provides its SEF route.
     *
     * @param   int  $articleId  The target article ID.
     *
     * @return  array
     */
    private function getArticleMenuRoutes(int $articleId): array
    {
        if (isset($this->articleMenuRoutesCache[$articleId])) {
            return $this->articleMenuRoutesCache[$articleId];
        }

        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('title'),
                $db->quoteName('alias'),
                $db->quoteName('path'),
                $db->quoteName('link'),
                $db->quoteName('type'),
                $db->quoteName('published'),
                $db->quoteName('client_id'),
                $db->quoteName('language'),
            ])
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->order($db->quoteName('id') . ' ASC');

        $menuItems = $db->setQuery($query)->loadObjectList();
        $routes = [];

        foreach ($menuItems as $menuItem) {
            $link = (string) $menuItem->link;
            $queryString = (string) parse_url($link, PHP_URL_QUERY);

            $linkParameters = [];
            parse_str($queryString, $linkParameters);

            $option = $linkParameters['option'] ?? '';
            $view   = $linkParameters['view'] ?? '';
            $id     = isset($linkParameters['id'])
                ? (int) $linkParameters['id']
                : 0;

            if (
                $option !== 'com_content'
                || $view !== 'article'
                || $id !== $articleId
            ) {
                continue;
            }

            $route = $this->normaliseUrlPath(
                '/' . ltrim((string) $menuItem->path, '/')
            );

            if ($route === '') {
                continue;
            }

            $routes[] = $route;
        }

        $routes = array_values(array_unique($routes));

        $this->articleMenuRoutesCache[$articleId] = $routes;

        return $routes;
    }

    /**
     * Extract anchor links from HTML content.
     *
     * @param   string  $html  The HTML content to parse.
     *
     * @return  array
     */
    private function extractLinks(string $html): array
    {
        $links = [];

        if (trim($html) === '') {
            return $links;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $previousSetting = libxml_use_internal_errors(true);

        $htmlDocument = '<!DOCTYPE html><html><head>'
            . '<meta charset="UTF-8"></head><body>'
            . $html
            . '</body></html>';

        $loaded = $dom->loadHTML(
            $htmlDocument,
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        if (!$loaded) {
            return $links;
        }

        $xpath = new \DOMXPath($dom);
        $anchors = $xpath->query('//a[@href]');

        if ($anchors === false) {
            return $links;
        }

        foreach ($anchors as $anchor) {
            $href = trim((string) $anchor->getAttribute('href'));

            if ($href === '') {
                continue;
            }

            $links[] = [
                'href'    => $href,
                'text'    => trim((string) $anchor->textContent),
                'context' => $this->getLinkContext($anchor),
            ];
        }

        return $links;
    }

    /**
     * Get readable context around an anchor.
     *
     * @param   \DOMNode  $anchor  The anchor element.
     *
     * @return  string
     */
    private function getLinkContext(\DOMNode $anchor): string
    {
        $parent = $anchor->parentNode;

        if (!$parent) {
            return trim((string) $anchor->textContent);
        }

        $context = trim((string) $parent->textContent);

        if (function_exists('mb_substr')) {
            return mb_substr($context, 0, 500);
        }

        return substr($context, 0, 500);
    }

    /**
     * Determine whether an extracted link points to the target article.
     *
     * Matching order:
     * 1. Direct Joomla article ID in the URL query string.
     * 2. Published menu item route path.
     *
     * The article alias is deliberately not used for matching.
     *
     * @param   array   $link          The extracted link data.
     * @param   object  $targetArticle The target article.
     * @param   array   $targetRoutes  The target article's menu routes.
     *
     * @return  bool
     */
    private function linkPointsToArticle(
        array $link,
        object $targetArticle,
        array $targetRoutes
    ): bool {
        $href = trim((string) ($link['href'] ?? ''));

        if ($href === '') {
            return false;
        }

        // Ignore fragment-only and non-web links.
        if (
            str_starts_with($href, '#')
            || preg_match('/^(mailto|tel|javascript|data):/i', $href)
        ) {
            return false;
        }

        $hrefUrl = parse_url($href);

        if ($hrefUrl === false) {
            return false;
        }

        // Validate absolute URLs against the current site's host.
        if (!empty($hrefUrl['host'])) {
            $linkHost = $this->normaliseHost($hrefUrl['host']);

            $currentHost = $this->normaliseHost(
                (string) Uri::getInstance()->getHost()
            );

            if ($currentHost !== '' && $linkHost !== $currentHost) {
                return false;
            }
        }

        // Check direct Joomla article-ID links.
        $queryString = (string) ($hrefUrl['query'] ?? '');
        $parameters = [];

        parse_str($queryString, $parameters);

        $option = $parameters['option'] ?? '';
        $view   = $parameters['view'] ?? '';
        $id     = isset($parameters['id'])
            ? (int) $parameters['id']
            : 0;

        if (
            $option === 'com_content'
            && $view === 'article'
            && $id === (int) $targetArticle->id
        ) {
            return true;
        }

        // Compare the normalized path against the target article's menu routes.
        $hrefPath = $this->normaliseUrlPath(
            (string) ($hrefUrl['path'] ?? '')
        );

        return $hrefPath !== ''
            && in_array($hrefPath, $targetRoutes, true);
    }

    /**
     * Normalize a URL path for comparison.
     *
     * Query strings and hostnames are not part of the comparison.
     *
     * @param   string  $path  The URL path to normalize.
     *
     * @return  string
     */
    private function normaliseUrlPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        // Allow a full URL to be passed accidentally.
        $parsedPath = parse_url($path, PHP_URL_PATH);

        if (is_string($parsedPath)) {
            $path = $parsedPath;
        }

        $path = rawurldecode($path);
        $path = str_replace('\\', '/', $path);
        $path = '/' . ltrim($path, '/');

        // Remove a leading index.php if present.
        $path = preg_replace('#^/index\.php(?=/|$)#i', '', $path);

        // Normalize duplicate slashes and trailing slash.
        $path = preg_replace('#/+#', '/', $path);
        $path = rtrim($path, '/');

        return strtolower($path);
    }

    /**
     * Normalize hostnames so www and non-www versions compare equally.
     *
     * @param   string  $host  The hostname to normalize.
     *
     * @return  string
     */
    private function normaliseHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host);

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }
}