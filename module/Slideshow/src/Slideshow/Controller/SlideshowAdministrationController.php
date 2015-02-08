<?php
namespace Slideshow\Controller;

use Application\Controller\ApplicationAbstractAdministrationController;
use Zend\View\Model\ViewModel;

class SlideshowAdministrationController extends ApplicationAbstractAdministrationController
{
    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Slideshow\Model\SlideshowAdministration');
        }

        return $this->model;
    }

    /**
     * Slideshow categories list 
     */
    public function listCategoriesAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        $request = $this->getRequest();

        $filters = [];

        // get a filter form
        $filterForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Slideshow\Form\SlideshowCategoryFilter');

        $request = $this->getRequest();
        $filterForm->getForm()->setData($request->getQuery(), false);

        // check the filter form validation
        if ($filterForm->getForm()->isValid()) {
            $filters = $filterForm->getForm()->getData();
        }

        // get data
        $paginator = $this->getModel()->getCategories($this->getPage(),
                $this->getPerPage(), $this->getOrderBy(), $this->getOrderType(), $filters);

        return new ViewModel([
            'filter_form' => $filterForm->getForm(),
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ]);
    }

    /**
     * Browse images 
     */
    public function browseImagesAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        // get the category info
        if (null == ($category = $this->
                getModel()->getCategoryInfo($this->getSlug()))) {

            return $this->redirectTo('slideshow-administration', 'list-categories');
        }

        // get data
        $paginator = $this->getModel()->getImages($category['id'], 
                $this->getPage(), $this->getPerPage(), $this->getOrderBy(), $this->getOrderType());

        return new ViewModel([
            'category' => $category,
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ]);
    }

    /**
     * Add an image action
     */
    public function addImageAction()
    {
        // get the category info
        if (null == ($category = $this->
                getModel()->getCategoryInfo($this->params()->fromQuery('category', -1)))) {

            return $this->redirectTo('slideshow-administration', 'list-categories');
        }

        // get an image form
        $imageForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Slideshow\Form\SlideshowImage');

        $request  = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // fill the form with received values
            $imageForm->getForm()->setData($post, false);

            // save data
            if ($imageForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // add the image
                if (true === ($result = $this->getModel()->addImage($category['id'], 
                        $imageForm->getForm()->getData(), $this->params()->fromFiles('image')))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Image has been added'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('slideshow-administration', 'add-image', [], false, [
                    'category' => $category['id']
                ]);
            }
        }

        return new ViewModel([
            'category' => $category,
            'image_form' => $imageForm->getForm()
        ]);
    }

    /**
     * Add a new category action
     */
    public function addCategoryAction()
    {
        // get a category form
        $categoryForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Slideshow\Form\SlideshowCategory')
            ->setModel($this->getModel());

        $request  = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // fill the form with received values
            $categoryForm->getForm()->setData($request->getPost(), false);

            // save data
            if ($categoryForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // add a new category
                if (true === ($result = $this->getModel()->addCategory($categoryForm->getForm()->getData()))) {
                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Category has been added'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('slideshow-administration', 'add-category');
            }
        }

        return new ViewModel([
            'category_form' => $categoryForm->getForm()
        ]);
    }

    /**
     * Edit a category action
     */
    public function editCategoryAction()
    {
        // get the category info
        if (null == ($category = $this->
                getModel()->getCategoryInfo($this->getSlug()))) {

            return $this->redirectTo('slideshow-administration', 'list-categories');
        }

        // get the category form
        $categoryForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('Slideshow\Form\SlideshowCategory')
            ->setModel($this->getModel())
            ->setCategoryId($category['id']);

        $categoryForm->getForm()->setData($category);

        $request = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // fill the form with received values
            $categoryForm->getForm()->setData($request->getPost(), false);

            // save data
            if ($categoryForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // edit the category
                if (true === ($result = $this->
                        getModel()->editCategory($category['id'], $categoryForm->getForm()->getData()))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Category has been edited'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('slideshow-administration', 'edit-category', [
                    'slug' => $category['id']
                ]);
            }
        }

        return new ViewModel([
            'category' => $category,
            'category_form' => $categoryForm->getForm()
        ]);
    }
}