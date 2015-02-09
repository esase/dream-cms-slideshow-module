<?php
namespace Slideshow\Model;

use Application\Model\ApplicationAbstractBase;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;

class SlideshowBase extends ApplicationAbstractBase
{
    /**
     * Images directory
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
     * Delete an image
     *
     * @param string $imageName
     * @return boolean
     */
    protected function deleteImage($imageName)
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