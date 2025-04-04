<?php

namespace Serfhos\MyConfigurableRoutes\Service;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * TCA: itemsProcFunc option items
 */
class TableConfigurationService
{
    protected SiteFinder $siteFinder;
    protected ConfigurableRouteSiteService $configurableRouteSiteService;

    public function __construct(SiteFinder $siteFinder, ConfigurableRouteSiteService $configurableRouteSiteService)
    {
        $this->siteFinder = $siteFinder;
        $this->configurableRouteSiteService = $configurableRouteSiteService;
    }

    /**
     * Add configurable route enhancers to $parameters[&items]
     *
     * @used-by EXT:my_configurable_routes/Configuration/TCA/Overrides/pages.php -> my_configurable_routes_type
     */
    public function addPluginRouteOptions(array &$parameters): void
    {
        $site = $this->getSiteForRow($parameters['row'] ?? []);
        if ($site) {
            foreach ($this->configurableRouteSiteService->getAllRouteEnhancers($site) as $enhancer) {
                $parameters['items'][] = [
                    'label' => $enhancer->getLabel(),
                    'value' => $enhancer->getKey(),
                    // @extensionScannerIgnoreLine
                    'icon' => $enhancer->getIcon(),
                ];
            }
        }
    }

    protected function getSiteForRow(array $row): ?Site
    {
        if (empty($row)) {
            return null;
        }

        try {
            $id = MathUtility::canBeInterpretedAsInteger($row['uid']) ? $row['uid'] : $row['pid'];

            return $this->siteFinder->getSiteByPageId((int)$id);
        } catch (SiteNotFoundException $e) {
            // Never throw site not found exception
        }

        return null;
    }
}
