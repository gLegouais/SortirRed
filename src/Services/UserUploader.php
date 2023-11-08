<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserUploader
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly CampusRepository $campusRepository,
        private readonly EntityManagerInterface $manager,
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    public function uploadUsers(UploadedFile $usersCSV): int
    {
        $nbUsersAdded = 0;
        $usersArray = file($usersCSV, FILE_IGNORE_NEW_LINES);
        $headers = explode(';', $usersArray[0]);
        $usersMapping = [];
        for ($i = 1; $i < count($usersArray); $i++) {
            $userData = explode(';', $usersArray[$i]);
            for ($j = 0; $j < count($headers); $j++) {
                $usersMapping[$headers[$j]] = $userData[$j];
            }
            $campus = $this->campusRepository->findOneBy(['name' => $usersMapping['campus']]);
            $user = $this->serializer->denormalize($usersMapping, User::class);
            $password = $this->hasher->hashPassword($user, '123456');
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $user->setCampus($campus);
            $this->manager->persist($user);
            $this->manager->flush();
            $nbUsersAdded++;
        }
        return $nbUsersAdded;
    }

}