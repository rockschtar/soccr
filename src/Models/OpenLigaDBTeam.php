<?php

namespace Rockschtar\Soccr\Models;

use JsonSerializable;

class OpenLigaDBTeam implements JsonSerializable
{
    private int $teamId;

    private string $teamName;

    private ?string $shortName = null;

    private ?string $iconUrl = null;

    private ?string $teamGroupName = null;

    public function getTeamId(): int
    {
        return $this->teamId;
    }

    public function setTeamId(int $teamId): OpenLigaDBTeam
    {
        $this->teamId = $teamId;
        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): OpenLigaDBTeam
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function getTeamName(): string
    {
        return $this->teamName;
    }

    public function setTeamName(string $teamName): OpenLigaDBTeam
    {
        $this->teamName = $teamName;
        return $this;
    }

    public function getIconUrl(): ?string
    {
        return apply_filters('soccr_team_icon_url', $this->iconUrl, $this->getTeamId());
    }

    public function setIconUrl(?string $iconUrl): OpenLigaDBTeam
    {
        $this->iconUrl = $iconUrl;
        return $this;
    }

    public function getTeamGroupName(): ?string
    {
        return $this->teamGroupName;
    }

    public function setTeamGroupName(?string $teamGroupName): OpenLigaDBTeam
    {
        $this->teamGroupName = $teamGroupName;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'teamId' => $this->getTeamId(),
            'teamName' => $this->getTeamName(),
            'shortName' => $this->getShortName(),
            'iconUrl' => $this->getIconUrl(),
            'teamGroupName' => $this->getTeamGroupName(),
        ];
    }
}
