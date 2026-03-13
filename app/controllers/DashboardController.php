<?php

require_once __DIR__ . '/../repositories/DashboardRepository.php';
require_once __DIR__ . '/../config/Database.php';

class DashboardController
{
    public function index()
    {
        $db = Database::getInstance()->getConnection();

        $dashboardRepo = new DashboardRepository($db);

        $data = [
            'totalClients' => $dashboardRepo->countClients(),
            'totalPets' => $dashboardRepo->countPets(),
            'todayConsultations' => $dashboardRepo->todayConsultations(),
            'todaySales' => $dashboardRepo->todaySales(),
            'recentConsultations' => $dashboardRepo->recentConsultations(),
            'recentSales' => $dashboardRepo->recentSales(),
            'lowStockMedications' => $dashboardRepo->lowStockMedications(),
            'monthlySales' => $dashboardRepo->monthlySales()
        ];

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}