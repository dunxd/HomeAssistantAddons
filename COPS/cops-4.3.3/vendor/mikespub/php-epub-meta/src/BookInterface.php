<?php

namespace SebLucas\EPubMeta;

interface BookInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * @return array<string>
     */
    public function getAuthors();

    /**
     * @param array<string>|string $authors
     * @return void
     */
    public function setAuthors($authors);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * @return array<string>
     */
    public function getSubjects();

    /**
     * @param array<string>|string $subjects
     * @return void
     */
    public function setSubjects($subjects);

    /**
     * @return string
     */
    public function getPublisher();

    /**
     * @param string $publisher
     * @return void
     */
    public function setPublisher($publisher);

    /**
     * @return string
     */
    public function getLanguage();

    /**
     * @param string $lang
     * @return void
     */
    public function setLanguage($lang);

    /**
     * @return string
     */
    public function getIsbn();

    /**
     * @param string $isbn
     * @return void
     */
    public function setIsbn($isbn);

    /**
     * @return array<mixed>
     */
    public function getCoverInfo();

    /**
     * @param string $path
     * @param string|null $mime
     * @return void
     */
    public function setCover($path, $mime);

    /**
     * @return void
     */
    public function save();

    /**
     * @return string
     */
    public function file();
}
