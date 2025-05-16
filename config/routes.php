<?php

/**
 * @OA\Info(
 *   title="OpenAPI Docs for VampyreBytes's Outgunned API. -- Compatible with Director's Cut",
 *   description="This is an API for the Director's Cut system, specifically Outgunned.",
 *   version="1.0.0",
 *   @OA\Contact(
 *     name="Vampyre Bytes",
 *     email="admin@vampyrebytes.com"
 *   )
 * )
 *
 * @OA\Server(
 *     url="https://ogapi.vampyrebytes.com"
 * )
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

use Outgunned\Generate\NPC;
use CommonRoutes\Generate\Gender;
use CommonRoutes\Generate\Name;
use CommonRoutes\Generate\Occupation;
use CommonRoutes\Generate\PhysicalDescription;
use CommonRoutes\Generate\Voice;
use Outgunned\Generate\Dice\Pool;

return function (App $app) {
    $app->get('/', function (
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $response->getBody()->write(json_encode(["Refer to the documentation at /openapi"], JSON_PRETTY_PRINT));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    /**
     * @OA\Get(
     *     path="/openapi.json",
     *     summary="Returns the OpenAPI 3.0 documentation in JSON format.",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response="200",
     *         description="The OpenAPI 3.0 documentation in JSON format. This documentation provides details on all
     * available API endpoints, including request parameters, response data, and response codes. The file is generated
     * dynamically based on the API documentation provided in the code base.",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     )
     * )
     */
    $app->get('/openapi.json', function ($request, $response, $args) {
        $swagger = OpenApi\Generator::scan([__DIR__]);
        $response->getBody()->write(json_encode($swagger, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->get('/openapi', function ($request, $response, $args) {
        $swagger = OpenApi\Generator::scan([__DIR__]);
        $response->getBody()->write(json_encode($swagger, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    });
    /**
     * @OA\Get(
     *     path="/openapi.yaml",
     *     summary="Returns the OpenAPI 3.0 documentation in YAML format.",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response="200",
     *         description="The OpenAPI 3.0 documentation in YAML format. This documentation provides details on all
     * available API endpoints, including request parameters, response data, and response codes. The file is generated
     * dynamically based on the API documentation provided in the code base.",
     *         @OA\MediaType(
     *             mediaType="application/x-yaml",
     *             @OA\Schema(
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    $app->get('/openapi.yaml', function ($request, $response, $args) {
        $swagger = OpenApi\Generator::scan([__DIR__]);
        $response->getBody()->write($swagger->toYaml());
        return $response->withHeader('Content-Type', 'application/x-yaml');
    });

// Dice Roller!
    /**
     * @OA\Get(
     *     path="/dice_pool/{total}/{hunger}/{difficulty}",
     *     summary="Rolls a pool of V5 dice including hunger dice against a difficulty.",
     *     tags={"Dice Roller"},
     *     @OA\Parameter(
     *         name="total",
     *         in="path",
     *         description="Total number of dice to roll.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hunger",
     *         in="path",
     *         description="Number of hunger dice in the pool.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="difficulty",
     *         in="path",
     *         description="The difficulty threshold for the roll to be considered successful.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dice roll results",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="regularResults",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 description="Results of the regular dice rolls."
     *             ),
     *             @OA\Property(
     *                 property="hungerResults",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 description="Results of the hunger dice rolls."
     *             ),
     *             @OA\Property(
     *                 property="totalSuccesses",
     *                 type="integer",
     *                 description="Total number of successes achieved."
     *             ),
     *             @OA\Property(
     *                 property="criticalSuccesses",
     *                 type="integer",
     *                 description="Number of critical successes achieved."
     *             ),
     *             @OA\Property(
     *                 property="hungerCriticals",
     *                 type="integer",
     *                 description="Number of critical successes achieved with hunger dice."
     *             ),
     *             @OA\Property(
     *                 property="failures",
     *                 type="integer",
     *                 description="Number of failures, specifically from hunger dice."
     *             ),
     *             @OA\Property(
     *                 property="bestialFailure",
     *                 type="boolean",
     *                 description="Indicates if a bestial failure occurred."
     *             ),
     *             @OA\Property(
     *                 property="messyCritical",
     *                 type="boolean",
     *                 description="Indicates if a messy critical occurred."
     *             )
     *         )
     *     )
     * )
     */
    $app->get('/dice_pool/{total}/{hunger}/{difficulty}', Pool::class);

//Individual Generators
    /**
     * @OA\Get(
     *     path="/name/{type}/{gender}",
     *     summary="Generates a 'real world' name.",
     *     tags={"Generators"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description=">
     Name Part:
     * `first` - Given Name
     * `last` - Surname only (gender is ignored.)
     * `full` - Full Name (Given name + Surname only)
     * `null` - Full Name, possibly also including Titles and Suffixes",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             default=null,
     *             nullable=true,
     *             enum={"full", "first", "last", null}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="path",
     *         description=">
    Gender:
     * `male` - Names typically belonging to males as well as a few neutral that are common among AMAB persons. (This works with 'first', 'full', and null.)
     * `female` - Names typically belonging to females as well as a few neutral that are common among AFAB persons. (This works with 'first', 'full', and null.)
     * `neutral` - Names frequently chosen for being gender-neutral. (Please note that this only works with 'full' or 'first'. Does not work with null.)
     * `null` - Currently, only Male and Female names are available at this time. This does include several names that might be considered gender neutral.",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             default="any",
     *             enum={"male", "female", "neutral", "any"}
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="A randomly generated name",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe")
     *         )
     *     )
     * )
     */
    $app->get('/name/{type}/{gender}', Name::class);

    /**
     * @OA\Get(
     *     path="/gender",
     *     summary="Generates a random gender",
     *     tags={"Generators"},
     *     @OA\Response(
     *         response="200",
     *         description="Random gender",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="gender",
     *                 type="string",
     *                 enum={
     *                     "Male",
     *                     "Female",
     *                     "Non-Binary",
     *                     "Genderqueer",
     *                     "Agender",
     *                     "Bigender",
     *                     "Androgynous",
     *                     "Intersex",
     *                     "Genderfluid",
     *                     "Neutrois",
     *                     "Pangender",
     *                     "Two-Spirit",
     *                     "Transgender"
     *                 }
     *              )
     *         )
     *     )
     * )
     */
    $app->get('/gender', Gender::class);

    /**
     * @OA\Get(
     *     path="/voice/{laban}",
     *     summary="Generates a Vocal pattern, based on, but not limited to, Laban Style for voice acting.",
     *     tags={"Generators"},
     *     @OA\Parameter(
     *         name="laban",
     *         in="path",
     *         description="Indicates whether to generate a voice pattern based on Laban (true) or a comprehensive set (false).",
     *         required=true,
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Possible Voice Variations.",
     *         @OA\JsonContent(
     *             @OA\Property(property="base_voice", type="string", example="Thrusting - Heavy, Indirect, Sudden", description="A string containing the base voice pattern, consisting of 3 factors and/or a Laban style."),
     *             @OA\Property(property="add_ons", type="object",
     *                 @OA\Property(property="Air Source", type="string", example="Nasal"),
     *                 @OA\Property(property="Air Variant", type="string", example="Dry"),
     *                 @OA\Property(property="Age Variant", type="string", example="Child"),
     *                 @OA\Property(property="Body Size", type="string", example="Large"),
     *                 @OA\Property(property="Tempo", type="string", example="Slow"),
     *                 @OA\Property(property="Tone", type="string", example="Friendly"),
     *                 @OA\Property(property="Impairments", type="string", example="Mild")
     *             )
     *         )
     *     )
     * )
     */
    $app->get('/voice[/{laban}]', Voice::class);

    /**
     * @OA\Get(
     *     path="/physical_description/{gender}",
     *     summary="Generates a physical description",
     *     tags={"Generators"},
     *     @OA\Parameter(
     *         name="gender",
     *         in="path",
     *         description="The gender for which to generate the physical description. (Currently irrelevant.)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description=">
Physical description. The build descriptor should be roughly accurate for the calculated BMI.
* NB: Some of these descriptors may have different connotations and are not necessarily accurate or appropriate in all contexts.
It's important to use language thoughtfully and respectfully, and to avoid stigmatizing or derogatory terms.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="age", type="string", example="27 years old"),
     *             @OA\Property(property="height", type="string", example="177 cm / 70 inches"),
     *             @OA\Property(property="weight", type="string", example="68 kg / 150 lbs"),
     *             @OA\Property(property="bmi", type="number", format="float", example=19.31),
     *             @OA\Property(property="build", type="string", example="athletic"),
     *             @OA\Property(property="skinTone", type="string", example="fair"),
     *             @OA\Property(property="hairColor", type="string", example="brown"),
     *             @OA\Property(property="eyeColor", type="string", example="brown"),
     *             @OA\Property(property="facialFeatures", type="string", example="scar on left cheek"),
     *             @OA\Property(property="noticeableMarkings", type="string", example="tattoo on right arm"),
     *             @OA\Property(property="clothingStyle", type="string", example="casual")
     *         )
     *     )
     * )
     */
    $app->get('/physical_description[/{gender}]', PhysicalDescription::class);

    /**
     * @OA\Get(
     *     path="/occupation",
     *     summary="Generate a random occupation",
     *     tags={"Generators"},
     *     @OA\Response(
     *         response=200,
     *         description="Returns a JSON object with a random occupation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="occupation",
     *                 type="string",
     *                 description="The generated occupation"
     *             )
     *         )
     *     )
     * )
     */
    $app->get('/occupation', Occupation::class);

//Collections
    /**
     * @OA\Get(
     *     path="/npc",
     *     summary="Generate a single NPC with randomized name, gender, physical descriptions, and voice",
     *     tags={"Collections"},
     *     @OA\Response(
     *         response="200",
     *         description="NPC information",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Alexandra"),
     *             @OA\Property(property="gender", type="string"),
     *             @OA\Property(
     *                 property="physicalDescription",
     *                 type="object",
     *                 @OA\Property(property="height", type="string", example="71 inches"),
     *                 @OA\Property(property="weight", type="string", example="150 pounds"),
     *                 @OA\Property(property="bmi", type="number", format="float", example=26.27),
     *                 @OA\Property(property="build", type="string", example="athletic"),
     *                 @OA\Property(property="skinTone", type="string", example="fair"),
     *                 @OA\Property(property="hairColor", type="string", example="brown"),
     *                 @OA\Property(property="eyeColor", type="string", example="brown")
     *             ),
     *             @OA\Property(
     *                 property="vocal_tips",
     *                 type="object",
     *                 @OA\Property(
     *                     property="base_voice",
     *                     type="string",
     *                     example="Dabbing - Light, Direct, Sudden",
     *                     description="A string containing the base voice pattern, consisting of 3 factors and/or a Laban style."
     *                 ),
     *                 @OA\Property(property="add_ons", type="object",
     *                     @OA\Property(property="Air Source", type="string", example="Nasal"),
     *                     @OA\Property(property="Air Variant", type="string", example="Dry"),
     *                     @OA\Property(property="Age Variant", type="string", example="Child"),
     *                     @OA\Property(property="Body Size", type="string", example="Large"),
     *                     @OA\Property(property="Tempo", type="string", example="Slow"),
     *                     @OA\Property(property="Tone", type="string", example="Friendly"),
     *                     @OA\Property(property="Impairments", type="string", example="Mild")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    $app->get('/npc', NPC::class);

};