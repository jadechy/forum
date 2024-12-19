<?php

namespace App\DataFixtures;
use App\Entity\Topic;
use App\Entity\Language;
use App\Entity\Category;
use App\Entity\Response;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const MAX_USERS = 10;
    public const MAX_ADMIN_USERS = 2;
    public const MAX_BANNED_USERS = 3;

    public const MAX_TOPICS = 20;

    public const MAX_RESPONSES_PER_TOPICS = 5;

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $users = [];
        $categories = [];
        $languages = [];
        $topics = [];
        $responses = [];

        $this->createUsers($manager, $users);
        $this->createAdminUsers($manager, $users);
        $this->createBannedUsers($manager, $users);

        $this->createCategories($manager, $categories);
        $this->createLanguages($manager, $languages);
        $this->createTopics($manager, $topics);
        $this->createResponses($manager, $topics, $users); 

        $this->linkTopicsToCategoriesAndLanguages($topics, $categories, $languages, $users);

        $manager->flush();
    }

    /**
     * @return User[] 
     */
    protected function createUsers(ObjectManager $manager, array &$users): void
    {
        for ($i = 0; $i < self::MAX_USERS; $i++) {
            $user = new User();
            $user->setEmail(email: "user_$i@example.com");
            $user->setUsername(username: "user_$i");
            $user->setPassword('coucou');
            $user->setRoles(['ROLE_USER']);

            $users[] = $user;

            $manager->persist(object: $user);
        }
    }

    /**
     * @return User[] 
     */
    protected function createAdminUsers(ObjectManager $manager, array &$users): void
    {
        for ($i = self::MAX_USERS; $i < self::MAX_USERS + self::MAX_ADMIN_USERS; $i++) {
            $user = new User();
            $user->setEmail(email: "user_$i@example.com");
            $user->setUsername(username: "user_$i");
            $user->setPassword('admin');
            $user->setRoles(['ROLE_USER','ROLE_ADMIN']);

            $users[] = $user;

            $manager->persist(object: $user);
        }
    }

    /**
     * @return User[] 
     */
    protected function createBannedUsers(ObjectManager $manager, array &$users): void
    {
        for ($i = self::MAX_USERS + self::MAX_ADMIN_USERS; $i < self::MAX_USERS + self::MAX_ADMIN_USERS + self::MAX_BANNED_USERS; $i++) {
            $user = new User();
            $user->setEmail(email: "user_$i@example.com");
            $user->setUsername(username: "user_$i");
            $user->setPassword('banned');
            $user->setRoles(['ROLE_BANNED']);

            $users[] = $user;

            $manager->persist(object: $user);
        }
    }

    protected function createCategories(ObjectManager $manager, array &$categories): void
    {
        $array = [
            ['nom' => 'Technologie', 'label' => 'Technologie'],
            ['nom' => 'Science', 'label' => 'Science'],
            ['nom' => 'Santé', 'label' => 'Santé'],
            ['nom' => 'Sport', 'label' => 'Sport'],
            ['nom' => 'Nature', 'label' => 'Nature'],
            ['nom' => 'Culture', 'label' => 'Culture'],
        ];

        foreach ($array as $element) {
            $category = new Category();
            $category->setName($element['nom']);
            $category->setLabel($element['label']);
            $manager->persist($category);
            $categories[] = $category;
        }
    }

    protected function createLanguages(ObjectManager $manager, array &$languages): void
    {
        $array = [
            ['code' => 'fr', 'nom' => 'Français'],
            ['code' => 'en', 'nom' => 'Anglais'],
            ['code' => 'es', 'nom' => 'Espagnol'],
            ['code' => 'de', 'nom' => 'Allemand'],
            ['code' => 'it', 'nom' => 'Italien'],
        ];

        foreach ($array as $element) {
            $language = new Language();
            $language->setCode($element['code']);
            $language->setName($element['nom']);
            $manager->persist($language);
            $languages[] = $language;
        }
    }

    protected function createTopics(ObjectManager $manager, array &$topics)
    {
        for ($j = 0; $j < self::MAX_TOPICS; $j++) {
            $topic = new Topic();

            $topic->setTitle(title: "Topic n°$j");
            $topic->setDescription(description: "Description $j");
            $topic->setCreatedAt(createdAt: new \DateTimeImmutable());
            $manager->persist(object: $topic);
            $topics[] = $topic;
        }
    }

    protected function createResponses(ObjectManager $manager, array $topics, array $users): void
    {
        /** @var Topic $topic */
        foreach ($topics as $topic) {
            for ($i = 0; $i < random_int(1, self::MAX_RESPONSES_PER_TOPICS); $i++) {
                $response = new Response();
                $response->setAuthor($users[array_rand($users)]);
                $response->setContent("Réponse $i");
                $response->setCreatedAt(createdAt: new \DateTimeImmutable());
                $response->setTopic($topic);

                $manager->persist($response);
            }
        }
    }

    protected function linkTopicsToCategoriesAndLanguages(array $topics, array $categories, array $languages, array $users): void
    {
        /** @var Topic $topic */
        foreach ($topics as $topic) {
            $topic->setCategory($categories[array_rand($categories)]);
            $topic->setLanguage($languages[array_rand($languages)]);

            $filteredUsers = array_filter($users, function ($user) {
                return in_array('ROLE_USER', $user->getRoles()) || in_array('ROLE_BANNED', $user->getRoles());
            });
    
            if (count($filteredUsers) > 0) {
                $topic->setAuthor($filteredUsers[array_rand($filteredUsers)]);
            }
        }
    }
}
