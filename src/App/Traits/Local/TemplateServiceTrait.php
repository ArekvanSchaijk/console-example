<?php
namespace AlterNET\Cli\App\Traits\Local;

use AlterNET\Cli\Local\Service\TemplateService;

/**
 * Class TemplateServiceTrait
 * @author Arek van Schaijk <arek@alternet.nl>
 */
trait TemplateServiceTrait
{

    /**
     * @var TemplateService
     */
    protected $templateService;

    /**
     * Gets the Template Service
     *
     * @return TemplateService
     */
    public function getTemplateService()
    {
        if (is_null($this->templateService)) {
            $this->templateService = new TemplateService();
        }
        return $this->templateService;
    }

}