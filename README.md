# yii2-cms-menu


#### backend/config/main.php

```
 'controllerMap' => [
        'menu' => [
            'class' => 'abdualiym\menu\controllers\MenuController',
        ],
 ],
 
```
#### frontend/controller/SiteController.php
```
private $slugHandler;
    private $categoryViewTemplate;

    public function __construct($id, $module, array $config = [])
    {
        $this->slugHandler = new SlugHandler(
            new TextTranslation(),
            new CategoryTranslation(),
            new Menu(),
            Language::getLangByPrefix(Yii::$app->language),
            Yii::$app->params['actions']
        );
        
        $this->categoryViewTemplate = [
            '_content',
            '_without_date_content',
            '_without_listing_content'
        ];

        parent::__construct($id, $module, $config);
    }
    
    
 public function actionChange($lang, $slug)
     {
         $l = Language::getLangByPrefix($lang);
         $explode = explode('/', $slug);
         $this->slugHandler->isAction($explode[0]);
         $this->redirect(MenuSlugHelper::generateSlug($explode, $l));
     }
 
     public function actionSlugRender($slug = null)
     {
         try {
 
             $result = $this->slugHandler->handler(
                         $slug,
                         $this->categoryViewTemplate
                       );
 
         } catch (\Exception $e) {
 
             throw new NotFoundHttpException('page not found :(');
         }
 
         return $this->render($result['template'], $result['data']);
 
 
     }

```

#### common/config/params.php
```
'languages' => ['en','ru'],
   'actions' => [
       [
           'name' => 'action name',
           'slug' => 'action slug',
           'action' => 'controller/action'
       ],
   ],

```
####  added url manager rules

```
'<lang:(uz|ru)>/<slug:[\w_\/-]+>' => 'site/change',

'<slug:[^(uz|ru)]+>/p/<page:\d+>' => 'site/slug-render',

'<slug:[^(uz|ru)]+>' => 'site/slug-render',

'<slug:[\w_\/-]+>' => 'site/slug-render',

```

