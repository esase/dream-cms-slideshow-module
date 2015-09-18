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
namespace Slideshow\Model;

use Slideshow\Exception\SlideshowException;
use Slideshow\Event\SlideshowEvent;
use Application\Utility\ApplicationErrorLogger;
use Application\Model\ApplicationAbstractBase;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Zend\Db\ResultSet\ResultSet;
use Exception;

class SlideshowBase extends ApplicationAbstractBase
{
    /**
     * Images directory
     *
     * @var string
     */
    protected static $imagesDir = 'slideshow/';

    /**
     * Get images directory name
     *
     * @return string
     */
    public static function getImagesDir()
    {
        return self::$imagesDir;
    }

    /**
     * Get unused categories
     *
     * @param integer $limit
     * @return array
     */
    public function getUnusedCategories($limit)
    {
        $select = $this->select();
        $select->from('slideshow_category')
            ->columns([
                'id',
                'name'
            ])
            ->limit($limit)
            ->where->and->isNull('language');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
    }

    /**
     * Get all categories
     * 
     * @param string $language
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getAllCategories($language = null)
    {
        $select = $this->select();
        $select->from('slideshow_category')
            ->columns([
                'id',
                'name',
                'language'
            ]);

      if ($language) {
            $select->where([
                'language' => $language
            ]);
      }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    /**
     * Delete a category
     *
     * @param array $categoryInfo
     *      integer id
     *      string name
     *      string language
     * @throws \Slideshow\Exception\SlideshowException
     * @return boolean|string
     */
    public function deleteCategory(array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            // get all images
            $select = $this->select();
            $select->from('slideshow_image')
               ->columns([
                   'image'
               ])
            ->where([
               'category_id' => $categoryInfo['id']
            ]);

            $statement = $this->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet;
            $resultSet->initialize($statement->execute());

            // delete assigned images
            foreach ($resultSet as $image) {
                // delete an image
                if (true !== ($imageDeleteResult = $this->deleteSlideShowImage($image['image']))) {
                   throw new SlideshowException('Image deleting failed');
                }
            }

            $delete = $this->delete()
                ->from('slideshow_category')
                ->where([
                    'id' => $categoryInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        $result =  $result->count() ? true : false;

        // fire the delete category event
        if ($result) {
            SlideshowEvent::fireDeleteCategoryEvent($categoryInfo['id']);
        }

        return $result;
    }

    /**
     * Delete an image
     *
     * @param array $imageInfo
     *      integer id
     *      string name
     *      string description
     *      integer category_id
     *      string image
     *      string url
     *      integer created
     * @throws \Slideshow\Exception\SlideshowException
     * @return boolean|string
     */
    public function deleteImage(array $imageInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $delete = $this->delete()
                ->from('slideshow_image')
                ->where([
                    'id' => $imageInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            // delete an image
            if (true !== ($imageDeleteResult = $this->deleteSlideShowImage($imageInfo['image']))) {
                throw new SlideshowException('Image deleting failed');
            }

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        $result =  $result->count() ? true : false;

        // fire the delete image event
        if ($result) {
            SlideshowEvent::fireDeleteImageEvent($imageInfo['id']);
        }

        return $result;
    }

    /**
     * Delete an image
     *
     * @param string $imageName
     * @return boolean
     */
    protected function deleteSlideShowImage($imageName)
    {
        if (true !== ($result = FileSystemUtility::deleteResourceFile($imageName, self::$imagesDir))) {
            return $result;
        }

        return true; 
    }

    /**
     * Get category info
     *
     * @param integer $id
     * @param boolean $currentLanguage
     * @return array
     */
    public function getCategoryInfo($id, $currentLanguage = true)
    {
        $select = $this->select();
        $select->from('slideshow_category')
            ->columns([
                'id',
                'name'
            ])
            ->where([
                'id' => $id
            ]);

        if ($currentLanguage) {
            $select->where([
                'language' => $this->getCurrentLanguage()
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }

    /**
     * Get image info
     *
     * @param integer $id
     * @param boolean $currentLanguage
     * @return array
     */
    public function getImageInfo($id, $currentLanguage = true)
    {
        $select = $this->select();
        $select->from(['a' => 'slideshow_image'])
            ->columns([
                'id',
                'name',
                'description',
                'category_id',
                'image',
                'url',
                'created',
            ])
            ->join(
                ['b' => 'slideshow_category'],
                'a.category_id = b.id',
                [
                    'category_name' => 'name'
                ]
            )
            ->where([
                'a.id' => $id
            ]);

        if ($currentLanguage) {
            $select->where([
                'b.language' => $this->getCurrentLanguage()
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }
}