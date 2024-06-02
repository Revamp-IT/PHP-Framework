---
description: Страница посвящена сущностям, которые существуют в Cheapy
---

# Архитектура

### Роут

Роут - главное свойство любого метода контроллера, объявляется аттрибутом `#[Route]` и содержит в себе `URI` и методы, которые будут доступны

### Модель запроса

Все запросы должны соответствовать указанной модели для того, чтобы исключить проверку наличия пришедших данных. Если каких либо данных не хватает - ответ будет экземпляром `JsonError` с кодом ответа `400` и шорткодом ошибки `R2`. Модель запроса указывается аттрибутом `#[Request]` в контроллере и содержит в себе `requestTemplate`. Вот пример модели запроса:

```php
// Request/Deal/CreateDealRequest.php

class CreateDealRequest extends RequestTemplate
{
    public string $name;
}
```

### Модель ответа

Все ответы должны соответствовать указанной модели для того, чтобы избежать непредвиденного поведения на клиенте. Если каких либо данных не хватает - ответ будет экземпляром `JsonError` с кодом ответа `400` и шорткодом ошибки `R3`. Модель запроса указывается аттрибутом `#[Response]` в контроллере и содержит в себе `responseTemplate`. Вот пример модели ответа:

```php
// Response/Deal/GetDealsByCompanyResponse.php

class GetDealsByCompanyResponse extends ResponseTemplate
{
    public string $deals;
}
```

### Объект базы данных

Работа с базой данных происходит благодаря ее сущностям, отраженным через объекты.&#x20;

```php
// DataMap/Deal.php

class Deal extends DataMapTemplate
{
    private string $name;
    private int $customer;
}
```

### Контроллер

Контроллер содержит в себе методы с бизнес логикой и ничего более.&#x20;

```php
// Controller/DealController.php

class DealController extends ControllerTemplate {
    #[Route(uri: '/deals/{id}', methods: ['GET'])]
    #[Request(requestTemplate: GetDealsByCompanyRequest::class)]
    #[Response(responseTemplate: GetDealsByCompanyResponse::class)]
    public function getDealsByCompany(): void
    {
        // some code
    
        $this->response->deals = [];
    }
}
```
