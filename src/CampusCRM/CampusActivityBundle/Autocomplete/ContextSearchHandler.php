<?php

namespace CampusCRM\CampusActivityBundle\Autocomplete;

use Oro\Bundle\ActivityBundle\Event\SearchAliasesEvent;

use Oro\Bundle\ActivityBundle\Autocomplete\ContextSearchHandler as BaseSearchHandler;
/**
 * This is specified handler that search targets entities for specified activity class.
 *
 * Can not use default Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface cause in this handler we manipulate
 * with different types of entities.
 *
 * Also @see Oro\Bundle\ActivityBundle\Form\DataTransformer\ContextsToViewTransformer
 */
class ContextSearchHandler extends BaseSearchHandler
{
    /**
     * Get search aliases for all entities which can be associated with specified activity.
     *
     * @return string[]
     */
    protected function getSearchAliases()
    {
        file_put_contents('/tmp/calendar.log', 'context search handler');

        $class               = $this->entityClassNameHelper->resolveEntityClass($this->class, true);
        $aliases             = [];
        $targetEntityClasses = array_keys($this->activityManager->getActivityTargets($class));

        foreach ($targetEntityClasses as $targetEntityClass) {
            $alias = $this->indexer->getEntityAlias($targetEntityClass);
            if (null !== $alias) {
                $aliases[] = $alias;
            }
        }

        if ($class == 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent') {
            $aliases = preg_grep("/^oro_sales_lead/", $aliases);
        }

            /** dispatch oro_activity.search_aliases event */
        $event = new SearchAliasesEvent($aliases, $targetEntityClasses);
        $this->dispatcher->dispatch(SearchAliasesEvent::EVENT_NAME, $event);
        $aliases = $event->getAliases();

        return $aliases;
    }
}
