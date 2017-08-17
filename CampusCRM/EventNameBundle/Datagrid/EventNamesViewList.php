<?php

namespace CampusCRM\EventNameBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class EventNamesViewList extends AbstractViewsList
{
    /**
     * {@inheritdoc}
     */
    protected function getViewsList()
    {
        return [
            (new View(
                'eventname.duplicities',
                ['duplicate' => ['value' => BooleanFilterType::TYPE_YES]],
                ['name' => 'ASC']
            ))
                ->setLabel($this->translator->trans(
                    'oro.datagrid.gridview.duplicate.label',
                    ['%entity%' => $this->translator->trans('oro.eventname.entity_label')]
                ))
        ];
    }
}
