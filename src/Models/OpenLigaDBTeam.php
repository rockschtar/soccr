<?php

namespace Rockschtar\WordPress\Soccr\Models;

use JsonSerializable;

class OpenLigaDBTeam implements JsonSerializable
{
    private int $teamId;

    private string $teamName;

    private ?string $shortName = null;

    private ?string $iconUrl = null;

    private ?string $teamGroupName = null;

    /**
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->teamId;
    }

    /**
     * @param int $teamId
     * @return OpenLigaDBTeam
     */
    public function setTeamId(int $teamId): OpenLigaDBTeam
    {
        $this->teamId = $teamId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * @param string|null $shortName
     * @return OpenLigaDBTeam
     */
    public function setShortName(?string $shortName): OpenLigaDBTeam
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTeamName(): string
    {
        return $this->teamName;
    }

    /**
     * @param string $teamName
     * @return OpenLigaDBTeam
     */
    public function setTeamName(string $teamName): OpenLigaDBTeam
    {
        $this->teamName = $teamName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    /**
     * @param string|null $iconUrl
     * @return OpenLigaDBTeam
     */
    public function setIconUrl(?string $iconUrl): OpenLigaDBTeam
    {
        $this->iconUrl = $iconUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTeamGroupName(): ?string
    {
        return $this->teamGroupName;
    }

    /**
     * @param string|null $teamGroupName
     * @return OpenLigaDBTeam
     */
    public function setTeamGroupName(?string $teamGroupName): OpenLigaDBTeam
    {
        $this->teamGroupName = $teamGroupName;
        return $this;
    }

    public function jsonSerialize() : array
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
