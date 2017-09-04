<?php

namespace CampusCRM\EventTopicsBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class EventTopicsViewList extends AbstractViewsList
{
    /**
     * {@inheritdoc}
     */
    protected function getViewsList()
    {
        return [
            (new View(
                'eventtopics.duplicities',
                ['duplicate' => ['value' => BooleanFilterType::TYPE_YES]],
                ['name' => 'ASC']
            ))
                ->setLabel($this->translator->trans(
                    'oro.datagrid.gridview.duplicate.label',
                    ['%entity%' => $this->translator->trans('oro.eventtopics.entity_label')]
                ))
        ];
    }
}
