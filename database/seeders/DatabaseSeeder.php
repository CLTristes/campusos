<?php

declare(strict_types=1);

namespace Database\Seeders;

use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder de DESENVOLVIMENTO — a instituição piloto e os atores da demo.
 *
 * Os dados vêm do histórico escolar real (docs_referencia/, não versionado):
 * UTFPR · Câmpus Francisco Beltrão · curso 25 (Sist. Informação) · matriz 45.
 *
 * O catálogo acadêmico (curso, matriz, disciplinas) NÃO nasce aqui: ele é
 * importado de documento pela coordenação — ver docs/dominio/DOCUMENTOS_ACADEMICOS.md.
 */
class DatabaseSeeder extends Seeder
{
    // SEM WithoutModelEvents, de propósito: esse trait desliga os eventos de
    // model -- inclusive o `creating` do Entityable, que é quem preenche
    // entity_ent_id a partir do TenantContext. Com ele ligado, todo seed de
    // model multi-tenant falha com "null value in column entity_ent_id".
    public function run(): void
    {
        $utfpr = Entity::query()->firstOrCreate(
            ['ent_name' => 'Universidade Tecnológica Federal do Paraná'],
            ['ent_email' => 'contato@utfpr.edu.br'],
        );

        // CLI não tem sessão HTTP: o contexto de tenant é restaurado à mão
        // (regra de ouro nº 4), senão o Entityable não preenche entity_ent_id.
        TenantContext::runAs($utfpr->ent_id, function () use ($utfpr): void {
            $fb = Campus::query()->firstOrCreate(
                ['cps_code' => 'FB'],
                ['cps_name' => 'Francisco Beltrão', 'cps_city' => 'Francisco Beltrão'],
            );

            User::query()->firstOrCreate(
                ['usr_email' => 'aluno@alunos.utfpr.edu.br'],
                [
                    'usr_name' => 'Felipe Kurt Pohling',
                    'usr_password' => 'campusos',
                    'usr_role' => UserRole::Student,
                    'usr_registration_number' => '2567857',
                    'campus_cps_id' => $fb->cps_id,
                ],
            );

            User::query()->firstOrCreate(
                ['usr_email' => 'coordenacao@utfpr.edu.br'],
                [
                    'usr_name' => 'Coordenação de Sistemas de Informação',
                    'usr_password' => 'campusos',
                    'usr_role' => UserRole::Coordinator,
                    'campus_cps_id' => $fb->cps_id,
                ],
            );

            $this->command?->info("  Instituição: {$utfpr->ent_name} (UTFPR)");
            $this->command?->info("  Câmpus: {$fb->cps_name} [{$fb->cps_code}]");
            $this->command?->info('  aluno@alunos.utfpr.edu.br / coordenacao@utfpr.edu.br — senha: campusos');
        });
    }
}
