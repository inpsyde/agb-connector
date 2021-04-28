<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document;


class Document implements DocumentInterface
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $language;
    /**
     * @var string
     */
    protected $type;

    /**
     * @var DocumentSettingsInterface
     */
    protected $settings;

    public function __construct(
        DocumentSettingsInterface $settings,
        string $title,
        string $htmlContent,
        string $country,
        string $language,
        string $type
    ){

        $this->title = $title;
        $this->content = $htmlContent;
        $this->country = $country;
        $this->language = $language;
        $this->type = $type;
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->content;
    }

    public function getSettings(): DocumentSettingsInterface
    {
        return $this->settings;
    }
}
