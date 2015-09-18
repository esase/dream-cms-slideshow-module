<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.dream-cms.kg/en/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Dream CMS software.
 * The Initial Developer of the Original Code is Dream CMS (http://www.dream-cms.kg).
 * All portions of the code written by Dream CMS are Copyright (c) 2014. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2014 Dream CMS. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Dream CMS software
 * Attribution URL: http://www.dream-cms.kg/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
namespace Slideshow\Form;

use Application\Service\Application as ApplicationService;
use Application\Form\ApplicationAbstractCustomForm;
use Application\Form\ApplicationCustomFormBuilder;
use Slideshow\Model\SlideshowBase as SlideshowBaseModel;

class SlideshowImage extends ApplicationAbstractCustomForm
{
    /**
     * Name max string length
     */
    const NAME_MAX_LENGTH = 50;

    /**
     * Description max string length
     */
    const DESCRIPTION_MAX_LENGTH = 255;

    /**
     * Url max string length
     */
    const URL_MAX_LENGTH = 100;

    /**
     * Form name
     *
     * @var string
     */
    protected $formName = 'slideshow-image';

    /**
     * List of ignored elements
     *
     * @var array
     */
    protected $ignoredElements = ['image'];

    /**
     * Image
     *
     * @var string
     */
    protected $image;

    /**
     * Form elements
     *
     * @var array
     */
    protected $formElements = [
        'name' => [
            'name' => 'name',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Name',
            'required' => true,
            'max_length' => self::NAME_MAX_LENGTH,
            'category' => 'General info'
        ],
        'image' => [
            'name' => 'image',
            'type' => ApplicationCustomFormBuilder::FIELD_IMAGE,
            'label' => 'Image',
            'required' => true,
            'extra_options' => [
                'file_url' => null,
                'preview' => false,
                'delete_option' => false
            ],
            'category' => 'General info'
        ],
        'description' => [
            'name' => 'description',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT_AREA,
            'label' => 'Description',
            'required' => false,
            'max_length' => self::DESCRIPTION_MAX_LENGTH,
            'category' => 'Miscellaneous info'
        ],
        'url' => [
            'name' => 'url',
            'type' => ApplicationCustomFormBuilder::FIELD_URL,
            'label' => 'Url',
            'required' => false,
            'max_length' => self::URL_MAX_LENGTH,
            'category' => 'Miscellaneous info'
        ],
        'csrf' => [
            'name' => 'csrf',
            'type' => ApplicationCustomFormBuilder::FIELD_CSRF
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Submit'
        ]
    ];

    /**
     * Get form instance
     *
     * @return \Application\Form\ApplicationCustomFormBuilder
     */
    public function getForm()
    {
        // get form builder
        if (!$this->form) {
            // add preview for the image
            if ($this->image) {
                $this->formElements['image']['required'] = false;
                $this->formElements['image']['extra_options']['preview'] = true;
                $this->formElements['image']['extra_options']['file_url'] =
                        ApplicationService::getResourcesUrl() . SlideshowBaseModel::getImagesDir() . $this->image;
            }

            $this->form = new ApplicationCustomFormBuilder($this->formName,
                        $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);
        }

        return $this->form;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return \Slideshow\Form\SlideshowImage
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}