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
        $this->menuBuilder = new MenuBuilder();
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

    public function testMenuTieneElementoMiPerfil(): void
    {
        $menu    = $this->menuBuilder->getTopMenu();
        $titulos = array_column($menu, 'title');
        $this->assertContains('Mi perfil', $titulos);
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

    public function testMenuTieneEstructuraCompleta(): void
    {
        $menu = $this->menuBuilder->getTopMenu();
        $this->assertCount(4, $menu, 'El menú principal debe tener 4 secciones: Inicio, Mi perfil, Catálogos, Reportes');
    }
}
