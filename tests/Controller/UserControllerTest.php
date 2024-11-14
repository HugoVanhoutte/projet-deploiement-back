<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private $jwtManager;
    private $userRepository;
    private function getValidJwtToken(): string
{
    $this->client->request(
        'POST',
        '/api/login',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode([
            'email' => 'test@aaa.fr',
            'password' => 'Admin59@'
        ])
    );

    $response = $this->client->getResponse();
    $data = json_decode($response->getContent(), true);

    // Debug: afficher le code de réponse et le contenu
    if ($response->getStatusCode() !== Response::HTTP_OK) {
        throw new \Exception('Authentication failed: ' . $response->getContent());
    }

    if (!isset($data['token'])) {
        throw new \Exception('JWT Token not found in response: ' . $response->getContent());
    }

    return $data['token'];
}
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testRegisterUserSuccess()
{
    $data = [
        'email' => 'testuser@example.com',
        'roles' => ['ROLE_USER'],
        'password' => 'StrongPassword123!@#', // Mot de passe très fort
        'username' => 'testuser',
        'phone' => '0123456789',
        'firstName' => 'aa',
        'lastName' => 'goAeqsqssqsqs'
    ];

    // Effectuer la requête POST
    $this->client->request(
        'POST',
        '/api/user/register',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode($data)
    );

    $response = $this->client->getResponse();
    $responseData = json_decode($response->getContent(), true);

    // Afficher la réponse complète pour le débogage
    if ($response->getStatusCode() !== Response::HTTP_CREATED) {
        echo "Response Code: " . $response->getStatusCode() . "\n";
        echo "Response Content: " . $response->getContent() . "\n";
    }

    // Assertions
    $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    $this->assertArrayHasKey('result', $responseData);
    $this->assertEquals('User registered successfully', $responseData['result']);
}
    protected function tearDown(): void
    {
        // Nettoyer la base de données après le test
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'testuser@example.com']);
        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        parent::tearDown();
    }
    public function testDeleteUserSuccessfully()
{
    $passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
    $token = $this->getValidJwtToken();

    // Créer un utilisateur à supprimer pour le test
    $userToDelete = new User();
    $userToDelete->setEmail('userToDelete@example.com');
    $hashedPassword = $passwordHasher->hashPassword($userToDelete, "StrongPassword123!@#");
    $userToDelete->setPassword('password'); // Utiliser un encodeur si nécessaire
    $this->entityManager->persist($userToDelete);
    $this->entityManager->flush();

    // Effectuer la requête DELETE avec le JWT
    $this->client->request(
        'DELETE',
        '/api/user/delete/' . $userToDelete->getId(),
        [],
        [],
        ['HTTP_Authorization' => 'Bearer ' . $token]
    );

    // Récupérer la réponse
    $response = $this->client->getResponse();
    $content = $response->getContent();

    // Vérifier le code de statut de la réponse
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    // Vérifier que le message contient "User and related ads deleted successfully"
    $this->assertStringContainsString('User and related ads deleted successfully', $content);

    // Vérifier que l'utilisateur a bien été supprimé de la base de données
    $deletedUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'userToDelete@example.com']);
    $this->assertNull($deletedUser);
}
    public function testDeleteUserUnauthorized()
    {
        // Essayer d'accéder à la route sans token
        $this->client->request('DELETE', '/api/user/delete/1');

        // Vérifier la réponse
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
    public function testDeleteNonExistentUser()
{
    $jwtToken = $this->getValidJwtToken();
    // Essayer de supprimer un utilisateur avec un ID inexistant
    $this->client->request(
        'DELETE',
        '/api/user/delete/999999',
        [],
        [],
        [
            'HTTP_Authorization' => 'Bearer ' . $jwtToken
        ]
    );

    $response = $this->client->getResponse();

    // Vérifier le statut de la réponse
    $this->assertEquals(401, $response->getStatusCode());

    // Vérifier que le message d'erreur contient 'User not found'
    $this->assertStringContainsString('User not found', $response->getContent());
}

public function testDeleteUserDatabaseError()
{
    $jwtToken = $this->getValidJwtToken();
    // Simuler une erreur de base de données en coupant l'EntityManager
    $this->entityManager->close();

    // Essayer de supprimer un utilisateur
    $this->client->request('DELETE', '/api/user/delete/1');
    $this->client->request(
        'DELETE',
        '/api/user/delete/1',
        [],
        [],
        [
            'HTTP_Authorization' => 'Bearer ' . $jwtToken
        ]
    );

    // Récupérer la réponse
    $response = $this->client->getResponse();

    // Vérifier le code de statut de la réponse
    $this->assertEquals(401, $response->getStatusCode());

    // Vérifier que le message d'erreur contient "Database error"
    $this->assertStringContainsString('Database error', $response->getContent());
}
}