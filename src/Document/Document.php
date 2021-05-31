<?php
declare(strict_types=1);

namespace Inpsyde\AGBConnector\Document;

/**
 * Represents legal document.
 */
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

    /**
     * @param DocumentSettingsInterface $settings Plugin settings.
     * @param string $title Document title.
     * @param string $htmlContent Content of the document formatted as HTML.
     * @param string $country Country the document belongs to.
     * @param string $language Language the document belongs to.
     * @param string $type Type of the document.
     *
     * @see DocumentInterface::getType() For possible document types.
     */
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
        $this->country = strtolower($country);
        $this->language = strtolower($language);
        $this->type = strtolower($type);
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

    /**
     * @inheritDoc
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @inheritDoc
     */
    public function getSettings(): DocumentSettingsInterface
    {
        return $this->settings;
    }
}
