<?php

namespace abdualiym\menu\controllers;

use abdualiym\languageClass\Language;
use abdualiym\menu\forms\menu\MenuForm;
use abdualiym\menu\services\MenuService;
use abdualiym\text\entities\CategoryTranslation;
use abdualiym\text\entities\TextTranslation;
use Yii;
use abdualiym\menu\entities\Menu;
use abdualiym\menu\entities\MenuSearch;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends Controller
{
    public $defaultRoute = 'menu';
    private $service;
    private $category;
    private $text;
    private $language;

    public function __construct($id, $module, MenuService $service, CategoryTranslation $category, TextTranslation $text, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->category = $category;
        $this->text = $text;
        $this->language = Language::getLangByPrefix(\Yii::$app->language);
    }


    public function getViewPath()
    {
        return Yii::getAlias('@vendor/abdualiym/yii2-menu/views/menu');
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Menu model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $menu = $this->findModel($id);
        $content = $this->findContent($menu);
        return $this->render('view', [
            'model' => $menu,
            'content' => $content
        ]);
    }

    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $form = new MenuForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $menu = $this->service->create($form);
                return $this->redirect(['view', 'id' => $menu->id]);
            } catch (\DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $menu = $this->findModel($id);
        $form = new MenuForm($menu);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            try {
                $this->service->edit($menu->id, $form);
                return $this->redirect(['view', 'id' => $menu->id]);
            } catch (\DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        return $this->render('update', [
            'model' => $form,
            'menu' => $menu,
        ]);
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            $this->service->remove($id);
        } catch (\DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function actionMoveUp($id)
    {
        try {
            $this->service->moveUp($id);
            Yii::$app->session->setFlash('success', 'Успешно перемещен вверх <i class="glyphicon glyphicon-arrow-up"></i>');
        } catch (\LogicException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function actionMoveDown($id)
    {
        try {
            $this->service->moveDown($id);
            Yii::$app->session->setFlash('success', 'Успешно перемещен вниз <i class="glyphicon glyphicon-arrow-down"></i>');
        } catch (\LogicException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
    }


    public function actionActivate($id)
    {
        try {
            $this->service->activate($id);
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'id' => $id]);
    }


    public function actionDraft($id)
    {
        try {
            $this->service->draft($id);
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'id' => $id]);
    }


    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($menu = Menu::find()->with('translations')->where(['id' => $id])->one()) !== null) {


            return $menu;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findContent($menu)
    {

        $result = $menu->type == 'category'
            ?
            $this->category::find()
                ->with('category')
                ->where(['parent_id' => $menu->type_helper, 'lang_id' => $this->language['id']])
                ->one()
            :
            $this->text::find()
                ->with('text')
                ->where(['parent_id' => $menu->type_helper, 'lang_id' => $this->language['id']])
                ->one();

        return $result;
    }
}
