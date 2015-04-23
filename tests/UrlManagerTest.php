<?php

use yii\helpers\Url;

class UrlManagerTest extends TestCase
{
    public function testSetDefaultLanguageIfNoLanguageSpecified()
    {
        $this->mockRequest('/');
        $this->mockComponent( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('', $request->pathInfo);
    }

    public function testSetLanguageFromUrl()
    {
        $this->mockRequest('/en-us/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanUseLanguageAliasInUrl()
    {
        $this->mockRequest('/deutsch/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'deutsch' => 'de'],
        ]);
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanUseLanguageWithWildcardCountryInUrl()
    {
        $this->mockRequest('/es-bo/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
        ]);
        $this->assertEquals('es-BO', Yii::$app->language);
        $this->assertEquals('es-BO', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('es-BO', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testRedirectsIfDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffix()
    {
        $this->expectRedirect('/site/page');
        $this->mockRequest('/en/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndDefaultLanguageUsesSuffix()
    {
        $this->expectRedirect('/en/site/page');
        $this->mockRequest('/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
            'enableDefaultSuffix' => true,
        ]);
    }

    public function testRedirectsIfLanguageWithUpperCaseCountryInUrl()
    {
        $this->expectRedirect('/es-bo/site/page');
        $this->mockRequest('/es-BO/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
        ]);
    }

    public function testRedirectsIfLanguageWithUpperCaseWildcardCountryInUrl()
    {
        $this->expectRedirect('/es-bo/site/page');
        $this->mockRequest('/es-BO/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageFound()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryFound()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-AT', 'de', 'en'],
        ]);
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithLowercaseCountryFound()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at', 'de', 'en'],
        ]);
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryOnlyMatchesLanguage()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at'],
        ]);
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInSession()
    {
        $this->expectRedirect('/de/site/page');
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockRequest('/site/page');
        $this->mockComponent( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlLanguageInCookie()
    {
        $this->expectRedirect('/de/site/page');
        $_COOKIE['_language'] = 'de';
        $this->mockRequest('/site/page');
        $this->mockComponent( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testCanDisableLanguageDetection()
    {
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguageDetection' => false,
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisablePersistence()
    {
        $this->mockRequest('/en-us/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
            'enablePersistence' => false,
        ]);
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertNull(Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisableCookieOnly()
    {
        $this->mockRequest('/en-us/site/page');
        $this->mockComponent([
            'languages' => ['en-US', 'en', 'de'],
            'languageCookieDuration' => false,
        ]);
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }
    public function testCreateUrlWithoutLanguageIfNoLanguageInUrl()
    {
        $this->mockRequest('/site/page');
        $this->mockComponent([
            'languages' => ['en-us', 'en', 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action']));
    }

    public function testCreateUrlWithLanguageFromUrl()
    {
        $this->mockRequest('/de/site/page');
        $this->mockComponent([
            'languages' => ['en-us', 'en', 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/de/demo/action'), Url::to(['/demo/action']));
    }

    public function testCreateUrlWithSpecificLanguage()
    {
        $this->mockRequest('/de/site/page');
        $this->mockComponent([
            'languages' => ['en-us', 'en', 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/en-us/demo/action'), Url::to(['/demo/action', 'language' => 'en-us']));
    }

    public function testCreateUrlWithSpecificAliasedLanguage()
    {
        $this->mockRequest('/fr/site/page');
        $this->mockComponent([
            'languages' => ['fr', 'en', 'deutsch' => 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/deutsch/demo/action'), Url::to(['/demo/action', 'language' => 'de']));
    }
}
