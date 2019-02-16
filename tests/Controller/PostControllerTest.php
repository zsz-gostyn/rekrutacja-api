<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Post;

class PostControllerTest extends WebTestCase
{
    private $entityManager;

    public function testShowAll()
    {
        $client = static::createClient();
        $client->request('GET', '/posts');
        $response = $client->getResponse();
        $this->assertJsonResponse($response);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('count', $data);
        $this->assertArrayHasKey('data', $data);
    }

    public function testAddValidPosts()
    {
        $postsResponses = static::addPosts(static::getValidPosts());
        
        foreach ($postsResponses as $response) {
            $this->entityManager->getConnection()->beginTransaction();
            
            $this->assertJsonResponse($response, 201);
            $json = json_decode($response->getContent(), true);
            
            $this->assertNotNull($json['data']);
            $newPostData = $json['data'];
            
            $this->assertArrayHasKeys($newPostData, ['id', 'topic', 'content', 'creation_date', 'active']);

            $post = $this->entityManager->getRepository(Post::class)->find($newPostData['id']);
            $this->assertNotNull($post);

            $this->assertEquals($post->getOrdinal(), $newPostData['ordinal']);
            $this->assertEquals($post->getTopic(), $newPostData['topic']);
            $this->assertEquals($post->getContent(), $newPostData['content']);
            $this->assertEquals($post->getCreationDate(), new \DateTime($newPostData['creation_date']));
            $this->assertEquals($post->getActive(), $newPostData['active']);
        
        
            $this->entityManager->getConnection()->rollBack();
        }

    }
    
    public function testAddInvalidPosts() {
        $postsResponses = static::addPosts(static::getInvalidPosts());

        foreach ($postsResponses as $response) {
            $this->assertJsonResponse($response, 400);
        }
    }

    public function assertJsonResponse($response, $status = 200)
    {
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
    }
    
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
    
    private function assertArrayHasKeys(array $array, array $keys)
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    private function addPosts(array $posts): array
    {
        $responses = [];
        $client = static::createClient();

        foreach ($posts as $post) {
            $json = json_encode($post);

            $client->request('POST', '/posts', [], [], ['CONTENT_TYPE' => 'application/json'], $json);
            $responses[] = $client->getResponse();
        }

        return $responses;
    }

    private function getValidPosts()
    {
        return [
            [
                'ordinal' => 124,
                'topic' => 'Testowa wiadomość',
                'content' => 'Treść testowej wiadomości z testu funkcjonalnego',
            ],
            [
                'ordinal' => 13,
                'topic' => 'Druga wiadomość',
                'content' => 'Treść innej testowej wiadomości',
                'active' => '',
            ],
        ];
    }

    private function getInvalidPosts()
    {
        return [
            [
                'ordinal' => 'string', // Incorrect ordinal type
                'topic' => 'Testowa wiadomość',
                'content' => 'Treść',
            ],
            [
                // No ordinal field in the request
                'topic' => 'Temat', 
                'content' => 'Treść',
            ],
            [
                'ordinal' => 15,
                // No topic field in the request
                'content' => 'Treść',
            ],
            [
                'ordinal' => 15,
                'topic' => '', // Blank topic
                'content' => 'Treść',
            ],
            [
                'ordinal' => 15,
                'topic' => str_repeat('x', 260), // Too big topic length
                'content' => 'Treść',
            ],
            [
                'ordinal' => 15,
                'topic' => 'Temat',
                // No content field in the request
            ],
            [
                'ordinal' => 15,
                'topic' => 'Temat',
                'content' => '', // Blank topic field
            ],
        ];
    }
}
