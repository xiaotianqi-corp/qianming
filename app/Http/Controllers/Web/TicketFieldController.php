<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TicketFieldController extends Controller
{
    // Listar todos los campos de ticket
    public function index(): JsonResponse
    {
        // Esto devolvería la definición de campos personalizados
        // Normalmente esto vendría de configuración o base de datos
        $fields = [
            [
                'id' => 1,
                'name' => 'category',
                'label' => 'Category',
                'type' => 'dropdown',
                'required' => false,
                'choices' => ['Hardware', 'Software', 'Network', 'Access']
            ],
            [
                'id' => 2,
                'name' => 'sub_category',
                'label' => 'Sub Category',
                'type' => 'dropdown',
                'required' => false,
                'choices' => []
            ],
            // Agregar más campos según configuración
        ];

        return response()->json(['fields' => $fields]);
    }
}