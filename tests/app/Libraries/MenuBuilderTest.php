<?php

namespace Tests\App\Libraries;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\MenuBuilder;

/**
 * Pruebas para MenuBuilder.
 *
 * MenuBuilder genera la estructura del menú de navegación
 * de forma estática, sin dependencias externas.
 */
class MenuBuilderTest extends CIUnitTestCase
{
    private MenuBuilder $menuBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuBuilder = new MenuBuilder($this->authorization(false));
    }

    private function authorization(bool $administrator): object
    {
        return new class ($administrator) {
            public function __construct(private bool $administrator) {}

            public function isUserInGroup(string $group): bool
            {
                return $this->administrator && strtolower($group) === 'administrador';
            }
        };
    }

    public function testGetTopMenuDevuelveArray(): void
    {
        $menu = $this->menuBuilder->getTopMenu();
        $this->assertIsArray($menu);
        $this->assertNotEmpty($menu);
    }

    public function testMenuTieneElementoInicio(): void
    {
        $menu    = $this->menuBuilder->getTopMenu();
        $titulos = array_column($menu, 'title');
        $this->assertContains('Inicio', $titulos);
    }

    public function testElMenuBuilderNoDuplicaElPerfilDelTopbar(): void
    {
        $menu    = $this->menuBuilder->getTopMenu();
        $titulos = array_column($menu, 'title');
        $this->assertNotContains('Mi perfil', $titulos);
        $this->assertNotContains('Perfil de Usuario', $titulos);
    }

    public function testCatalogosTieneSubmenu(): void
    {
        $menu      = $this->menuBuilder->getTopMenu();
        $catalogos = array_values(array_filter($menu, fn($item) => ($item['title'] ?? '') === 'Catálogos'));
        $this->assertNotEmpty($catalogos);
        $this->assertArrayHasKey('children', $catalogos[0]);
        $this->assertNotEmpty($catalogos[0]['children']);
    }

    public function testCatalogosSubmenuContieneOficinas(): void
    {
        $menu         = $this->menuBuilder->getTopMenu();
        $catalogos    = array_values(array_filter($menu, fn($item) => ($item['title'] ?? '') === 'Catálogos'));
        $hijos        = $catalogos[0]['children'];
        $hijosTitulos = array_filter(array_column($hijos, 'title'));
        $this->assertContains('Oficinas', $hijosTitulos);
    }

    public function testCatalogosTieneDivider(): void
    {
        $menu       = $this->menuBuilder->getTopMenu();
        $catalogos  = array_values(array_filter($menu, fn($item) => ($item['title'] ?? '') === 'Catálogos'));
        $hijos      = $catalogos[0]['children'];
        $hasDivider = false;
        foreach ($hijos as $item) {
            if (isset($item['divider']) && $item['divider'] === true) {
                $hasDivider = true;
                break;
            }
        }
        $this->assertTrue($hasDivider, 'Catálogos debe contener un divider');
    }

    public function testReportesTieneSubmenu(): void
    {
        $menu     = $this->menuBuilder->getTopMenu();
        $reportes = array_values(array_filter($menu, fn($item) => ($item['title'] ?? '') === 'Reportes'));
        $this->assertNotEmpty($reportes);
        $this->assertArrayHasKey('children', $reportes[0]);
        $this->assertNotEmpty($reportes[0]['children']);
    }

    public function testTodosLosItemsTienenIcono(): void
    {
        $menu = $this->menuBuilder->getTopMenu();
        foreach ($menu as $item) {
            if (isset($item['title'])) {
                $this->assertArrayHasKey('icon', $item, "Item '{$item['title']}' debe tener icono");
                $this->assertNotEmpty($item['icon'], "Item '{$item['title']}' debe tener icono no vacío");
            }
            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (isset($child['title'])) {
                        $this->assertArrayHasKey('icon', $child, "Subitem '{$child['title']}' debe tener icono");
                    }
                }
            }
        }
    }

    public function testMenuTieneEstructuraCompletaParaUsuarioNormal(): void
    {
        $menu = $this->menuBuilder->getTopMenu();
        $this->assertCount(4, $menu, 'El menú normal debe tener Inicio, Catálogos, Reportes y Procesos');
        $this->assertNotContains('Administración', array_column($menu, 'title'));
    }

    public function testMenuAdministradorIncluyeAdministracion(): void
    {
        $menu = (new MenuBuilder($this->authorization(true)))->getTopMenu();
        $titulos = array_column($menu, 'title');

        $this->assertCount(5, $menu);
        $this->assertContains('Administración', $titulos);

        $administracion = array_values(array_filter(
            $menu,
            static fn(array $item): bool => ($item['title'] ?? '') === 'Administración',
        ))[0];
        $adminChildren = array_column($administracion['children'], 'title');
        $this->assertContains('Usuarios', $adminChildren);
        $this->assertContains('Grupos de Usuarios', $adminChildren);
    }
}
