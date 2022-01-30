<?php
declare(strict_types=1);

use Api\Controllers\AccessMetierController;
use Api\Controllers\ActivityController;
use Api\Controllers\InfoController;


use Api\Controllers\InformationController;


use Api\Controllers\ThemeController;
use Api\Controllers\UserController;
use Api\Controllers\WsMetierController;
use Api\Middlewares\AdminMiddleware;
use Api\Middlewares\AuthentificationMiddleware;
use Api\Middlewares\SchemaMiddleware;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * format un callable pour Slim
 * @param string $controller
 * @param string $methode
 * @return string
 */
function cm(string $controller, string $methode): string
{
    return $controller . ':' . $methode;
}

return static function (App $app) {
    $app->get('/version', cm(InfoController::class, 'getVersion'));
    $app->get('/info', function (Request $req, Response $rep) {
        /** @noinspection ForgottenDebugOutputInspection */
        ob_start();
        phpinfo();
        $phpInfo = ob_get_clean();
        $rep->getBody()->write($phpInfo);
        return $rep;
    });
    $app->group("/{schema}", function (App $groupSchema) {
        $groupSchema->post("/account", cm(UserController::class, "createAccount"));
        $groupSchema->post("/login", cm(UserController::class, "connectUser"));
        $groupSchema->get("/enableAccount/{token:\\w+}", cm(UserController::class, "enableAccount"));
        $groupSchema->post("/forgotten", cm(UserController::class, "forgottenPassword"));
        $groupSchema->post("/recover", cm(UserController::class, "recoverPassword"));
        $groupSchema->delete("/logout", cm(UserController::class, "disconnect"));
        $groupSchema->group("", function (App $groupAuth) {
            $groupAuth->group("/me", function (App $profil) {
                $profil->patch("/password", cm(UserController::class, "updatePassword"));
                $profil->patch("", cm(UserController::class, "updateAccount"));
                $profil->post("/linked", cm(AccessMetierController::class, "linkMetierAccess"));
                $profil->get("/linked", cm(AccessMetierController::class, "getLinks"));
                $profil->delete("/linked/{id:\\d+}", cm(AccessMetierController::class, "deleteLink"));
                $profil->delete("/shared/{id:\\d+}", cm(AccessMetierController::class, "unsharedLink"));
            });
            $groupAuth->group('', function (App $groupAdmin) {
                // Routes concernant les wsMetier
                $groupAdmin->get("/wsMetier", cm(WsMetierController::class, "getList"));
                $groupAdmin->post("/wsMetier", cm(WsMetierController::class, "createWsMetier"));
                $groupAdmin->delete("/wsMetier/{id:\\d+}", cm(WsMetierController::class, "deleteWsMetier"));
                $groupAdmin->post("/wsMetier/{id:\\d+}", cm(WsMetierController::class, "updateWsMetier"));
                $groupAdmin->get("/wsMetier/{id:\\d+}/apiKey", cm(WsMetierController::class, "getApiKey"));
                $groupAdmin->post("/wsMetier/{id:\\d+}/apiKey", cm(WsMetierController::class, "updateApiKey"));
                $groupAdmin->post("/wsMetierFlag", cm(WsMetierController::class, "getwsMetierFlagsFromWs"));
            // Creation d'information
                $groupAdmin->post("/information", cm(InformationController::class, "createInformation"));
                $groupAdmin->post("/information/{id\\d+}", cm(InformationController::class, "updateInformation"));
                $groupAdmin->get("/information", cm(InformationController::class, "getAllInformation"));
                $groupAdmin->get("/information/public", cm(InformationController::class, "getAllInformationPublic"));
                $groupAdmin->get("/information/public/{id\\d+}", cm(InformationController::class, "getInformationPublic"));
                $groupAdmin->get("/information/{id\\d+}", cm(InformationController::class, "getInformation"));
                $groupAdmin->delete("/information/{id\\d+}", cm(InformationController::class, "deleteInformation"));

            // gestion des thèmes
                $groupAdmin->get("/infoClient", cm(ThemeController::class, "getInfoClient"));
                $groupAdmin->post("/theme", cm(ThemeController::class, "updateTheme"));
            })->add(AdminMiddleware::class);
            //Route des Activites
            $groupAuth->get("/activity/catalog", cm(ActivityController::class, "getCatalog"));
            $groupAuth->get("/activity", cm(ActivityController::class, "getInscription"));
        })->add(AuthentificationMiddleware::class);
        $groupSchema->get("/linked/{token:\\w+}", cm(AccessMetierController::class, "enablationLink"));
        $groupSchema->get("/shared/{token:\\w+}", cm(AccessMetierController::class, "deleteSharedWithToken"));
    })->add(SchemaMiddleware::class);
};
