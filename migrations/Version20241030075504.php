<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030075504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move id columns from integer to UUID';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE blog_post_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cv_professional_experience_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cv_project_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE place_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        
        $this->addSql('ALTER TABLE blog_post ALTER id DROP IDENTITY');
        $this->addSql('ALTER TABLE blog_post ALTER id TYPE VARCHAR');
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a8416e4485', 1]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a84192d7ec', 2]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a84252335e', 3]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a842d24b13', 4]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a843addae1', 5]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a843d41184', 6]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a843e8abc9', 7]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a8445b21c9', 8]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a84531eb52', 10]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a84610447b', 13]);
        $this->addSql('UPDATE blog_post SET id = ? WHERE id = ?', ['0192e6d4-94f4-7fd7-9b6c-72a846d7b58e', 14]);
        $this->addSql('ALTER TABLE blog_post ALTER id TYPE UUID USING id::uuid');
        
        $this->addSql('ALTER TABLE cv_professional_experience ALTER id DROP IDENTITY');
        $this->addSql('ALTER TABLE cv_professional_experience ALTER id TYPE VARCHAR');
        $this->addSql('UPDATE cv_professional_experience SET id = ? WHERE id = ?', ['0192e6d5-9ac3-7f9f-b8f5-63d6a043aaa4', 1]);
        $this->addSql('UPDATE cv_professional_experience SET id = ? WHERE id = ?', ['0192e6d5-9ac3-7f9f-b8f5-63d6a09b9d99', 2]);
        $this->addSql('UPDATE cv_professional_experience SET id = ? WHERE id = ?', ['0192e6d5-9ac3-7f9f-b8f5-63d6a1590d41', 3]);
        $this->addSql('UPDATE cv_professional_experience SET id = ? WHERE id = ?', ['0192e6d5-9ac4-7b94-a310-80628dbf076d', 4]);
        $this->addSql('ALTER TABLE cv_professional_experience ALTER id TYPE UUID USING id::uuid');
        
        $this->addSql('ALTER TABLE cv_project ALTER id DROP IDENTITY');
        $this->addSql('ALTER TABLE cv_project ALTER id TYPE VARCHAR');
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9a92dda75', 1]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9a9f52236', 2]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9aaaa4ff5', 3]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9aab52dbd', 4]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9ab3460b3', 5]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9ab3c4434', 6]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9ab539bd9', 7]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9ab6ff36d', 8]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9abb05ef9', 44]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9abbe6ccc', 77]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-72c4-7e34-ab86-42e9aca9a868', 78]);
        $this->addSql('UPDATE cv_project SET id = ? WHERE id = ?', ['0192e7ce-bc83-75bb-8fc4-656bccf25948', 79]);
        $this->addSql('ALTER TABLE cv_project ALTER id TYPE UUID USING id::uuid');
        
        $this->addSql('ALTER TABLE place ALTER id DROP IDENTITY');
        $this->addSql('ALTER TABLE place ALTER id TYPE VARCHAR');
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f2e6773c', 1]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f3879167', 2]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f45f1d92', 3]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f4a182e5', 4]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f564a366', 5]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f6587cc4', 6]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f672fa47', 7]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f6a662b9', 8]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183c-7fab-a03d-cb95f7311f43', 9]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f599906b6bd9', 10]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59990a6fc6e', 11]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59990cb4bfb', 12]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f599910c6b04', 13]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59991edde75', 14]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999278f619', 15]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f599929f6455', 16]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59993967ff8', 17]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999410a592', 18]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59994c72972', 19]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59995c2f3ce', 20]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59996a2b154', 21]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999722005c', 22]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59997f8bfdf', 23]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f599981b49d8', 24]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59998b2aca9', 25]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59998ca6d6f', 26]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f59999a83029', 27]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999aa1e44b', 28]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999b0172a4', 29]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999bbcf9e4', 30]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999c0fd1f5', 31]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999c27e2d9', 32]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183d-70f3-96da-f5999c29a833', 33]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564632d66c5', 34]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564638ed529', 35]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646470f8e8', 36]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-556464af8fbe', 37]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564659e0b0c', 38]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564660d43da', 39]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564669d350e', 40]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-556466bc093a', 41]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646730c733', 42]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564682d90be', 43]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564682fbbc7', 45]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-556468c05c09', 46]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-556468c91bda', 47]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-5564697223cb', 48]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-556469ce46ff', 49]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646a1b07d1', 50]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646a31e4c6', 51]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646b179fb7', 52]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646b2e98fc', 53]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646b4ac580', 54]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646c47940f', 55]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646c5404c2', 56]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646c748c9d', 84]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646c9b3d9c', 85]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646cf97083', 86]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646db9b4bf', 87]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-183e-72ed-88e6-55646e093207', 88]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5aa87bd6', 89]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5af43593', 90]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5b0c3e0e', 91]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5bf96a67', 92]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5c9c1576', 93]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5d1570d1', 94]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5d977464', 95]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5dcb1177', 96]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5e787389', 97]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5eebfaf7', 98]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5f72ce42', 99]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e5f95f5dd', 100]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e607f5473', 101]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e610e2d26', 103]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e614d6cdb', 104]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e6243824f', 105]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e624b8c8b', 107]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e63032c6b', 108]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e638a6069', 109]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e64086fda', 110]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e64f4286d', 111]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e6568c624', 112]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e664f2ea3', 113]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e66b48f4e', 114]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e672fa931', 115]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e67cecc2b', 116]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1840-7d69-801a-a37e68a13d8a', 117]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9212fccbe6c1', 118]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9212fce17971', 119]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9212fd5c5b6a', 120]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9212fe4763cc', 121]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9212fef59f4b', 122]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9212ff8bfc67', 123]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9213000b7a23', 124]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921301027054', 125]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9213017171be', 126]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921301d731f9', 128]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921301f74ab4', 129]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130226483b', 130]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921302c47e4e', 131]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-9213033a1366', 132]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130428979a', 133]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921304bea2ef', 134]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921305be7d0d', 135]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921306a6d0b6', 136]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130736c9f2', 137]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921307f57cd8', 138]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921308e2c858', 139]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-921309bb60a7', 140]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130ab0ea20', 141]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130b490319', 142]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130c182c27', 143]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130c2e50b7', 144]);
        $this->addSql('UPDATE place SET id = ? WHERE id = ?', ['0192e7d0-1842-7c2b-bfe1-92130d088635', 146]);
        $this->addSql('ALTER TABLE place ALTER id TYPE UUID USING id::uuid');

        $this->addSql('ALTER TABLE "user" ALTER id DROP IDENTITY');
        $this->addSql('ALTER TABLE "user" ALTER id TYPE VARCHAR');
        $this->addSql('UPDATE "user" SET id = ? WHERE id = ?', ['0192f616-8f2d-78d4-beef-060212d35d25', 1]);
        $this->addSql('ALTER TABLE "user" ALTER id TYPE UUID USING id::uuid');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE blog_post_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cv_professional_experience_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cv_project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE place_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE cv_project ALTER id TYPE INT');
        $this->addSql('ALTER TABLE cv_project ALTER id ADD GENERATED BY DEFAULT AS IDENTITY');
        $this->addSql('ALTER TABLE place ALTER id TYPE INT');
        $this->addSql('ALTER TABLE place ALTER id ADD GENERATED BY DEFAULT AS IDENTITY');
        $this->addSql('ALTER TABLE blog_post ALTER id TYPE INT');
        $this->addSql('ALTER TABLE blog_post ALTER id ADD GENERATED BY DEFAULT AS IDENTITY');
        $this->addSql('ALTER TABLE cv_professional_experience ALTER id TYPE INT');
        $this->addSql('ALTER TABLE cv_professional_experience ALTER id ADD GENERATED BY DEFAULT AS IDENTITY');
    }
}
