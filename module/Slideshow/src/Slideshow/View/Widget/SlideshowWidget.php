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
namespace Slideshow\View\Widget;

use Page\View\Widget\PageAbstractWidget;
use Acl\Service\Acl as AclService;

class SlideshowWidget extends PageAbstractWidget
{
    /**
     * Model instance
     *
     * @var \Slideshow\Model\SlideshowWidget
     */
    protected $model;

    /**
     * Get model
     *
     * @return \Slideshow\Model\SlideshowWidget
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Slideshow\Model\SlideshowWidget');
        }

        return $this->model;
    }

    /**
     * Include js and css files
     *
     * @return void
     */
    public function includeJsCssFiles()
    {
        $this->getView()->layoutHeadScript()->
                appendFile($this->getView()->layoutAsset('jquery.flexslider.js', 'js', 'slideshow'));

        $this->getView()->layoutHeadLink()->
                appendStylesheet($this->getView()->layoutAsset('flexslider.css', 'css', 'slideshow'));
    }

    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        if (AclService::checkPermission('slideshow_view', false) 
                && null != ($category = $this->getWidgetSetting('slideshow_category'))) {

            $images = $this->getModel()->getImages($category);

            if (count($images)) {
                AclService::checkPermission('slideshow_view', true);
                return $this->getView()->partial('slideshow/widget/slideshow', [
                    'enable_slideshow' => (int) $this->getWidgetSetting('slideshow_on'),
                    'images_width' => (int) $this->getWidgetSetting('slideshow_image_width'),
                    'images_height' => (int) $this->getWidgetSetting('slideshow_image_height'),
                    'images' => $images
                ]);
            }
        }

        return false;
    }
}