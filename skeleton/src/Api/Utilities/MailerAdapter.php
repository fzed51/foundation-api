<?php
declare(strict_types=1);

namespace Api\Utilities;

use RuntimeException;

/**
 * Class MailerAdapter
 * @package Api\Utilities
 */
abstract class MailerAdapter
{
    /**
     * @var string|null
     */
    protected ?string $currentTemplate =null;
    /**
     * @var string|null
     */
    protected ?string $title =null;
    /**
     * @var string|null
     */
    protected ?string $text =null;
    /**
     * @var string|null
     */
    protected ?string $html =null;
    /**
     * @param string $template
     * @param array<string, mixed> $data
     * @return string
     */
    protected function getbody(string $template, array $data):string
    {
        $this->initTemplate($template);
        $pattern = '[[%s]]';
        $map = [];
        foreach ($data as $var => $value) {
            $var=strtoupper($var);
            $map[sprintf($pattern, $var)] = htmlentities(strip_tags($value));
        }
        $str= strtr($this->html, $map);
        $this->detectForgottenProperties($str);
        return $str;
    }

    /**
     * @param string $template
     * @param array<string, mixed> $data
     * @return string
     */
    protected function getText(string $template, array $data):string
    {
        $this->initTemplate($template);
        $pattern = '[[%s]]';
        $map = [];
        foreach ($data as $var => $value) {
            $var=strtoupper($var);
            $map[sprintf($pattern, $var)] = strip_tags($value);
        }
        $str= strtr($this->text, $map);
        $this->detectForgottenProperties($str);
        return $str;
    }

    /**
     * @param string $template
     * @return string
     */
    protected function getTitle(string $template):string
    {
        $this->initTemplate($template);
        return $this->title;
    }

    /**
     * @param string $template
     *
     */
    protected function initTemplate(string $template):void
    {
        if ($this->currentTemplate === $template) {
            return;
        }
        $this->currentTemplate=$template;
        if (!is_dir("./template/email/")) {
            throw new RuntimeException("Le dossier template/email n'existe pas ");
        }
        if (!is_file("./template/email/".$this->currentTemplate.".txt")) {
            throw new RuntimeException("Le fichier template/email/".$this->currentTemplate.".txt n'existe pas ");
        }
        $content = file_get_contents("./template/email/".$this->currentTemplate.".txt");
        if (preg_match("/[\n\r]*(.*)\s*--\*--[\n\r]*(.*)\s*--\*--[\n\r]*(.*)\s*/s", $content, $matches) !== 1) {
            throw new RuntimeException("Le template ".$this->currentTemplate.".txt n'est pas valide");
        }
        $this->title=$matches[1];
        $this->text=$matches[2];
        $this->html=$matches[3];
    }

    /**
     * @param string $str
     */
    private function detectForgottenProperties(string $str):void
    {
        $res = preg_match("/\[\[\w+]]/", $str);
        if ($res >=1) {
            throw new RuntimeException("Un paramètre a été oublier dans le template : ".$this->currentTemplate);
        }
    }
}
