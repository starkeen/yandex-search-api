<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Grouping extends SimpleXMLElement
{
    public function getFound(): int
    {
        return (int)($this->found[0] ?? 0);
    }

    public function getFoundDocsHuman(): string
    {
        return (string)$this->{'found-docs-human'};
    }

    public function getGroupsOnPage(): int
    {
        return (int)($this->attributes()['groups-on-page']);
    }

    public function getCurrentPage(): int
    {
        return (int)$this->page;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): array
    {
        $groups = [];

        foreach ($this->group as $group) {
            $groups[] = new Group($group->asXML());
        }

        return $groups;
    }
}
