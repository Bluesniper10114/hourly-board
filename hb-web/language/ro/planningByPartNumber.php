<?php
return [
    "PlanningByPart" =>
        [
        "Header" => "Planificare per Part Number",
        "Title" => "Planificare pe lista de part numbere",
        "HeaderLabels" =>
            [
            "Pn" => "PN",
            "Initialquantity" => "Cantitate<br/>initiala",
            "RoutingASSYPN" => "Routing (minute)",
            "Total" => "Total",
            "A" => "A",
            "B" => "B",
            "C" => "C",
            "IMPORTEXCEL"=> "Import plan din Excel",
            "DISTRIBUTETOTAL"=>"Distribuie Total",
            "EXPORT" => "Exporta",
            "Save" => "Salveaza",
            "Validate" => 'Valideaza',
            "Add" => "Adauga",
            "Delete" => "Sterge",
        ],
         
    "Errors" =>
        [
        "Main" => "Verifica erorile",
        "Timeout" => "Daca nu sunt setate explicit, target-urile vor fi importate de la cel mai recent schimb disponibil",
        "Bottom" => "*Corecteaza toate erorile de mai sus pentru a putea salva",
        "Target" => "Capacitatea masinii a fost depasita",
        "QuantityMatch" => "Cantitatea initiala difera de target",
        "Availabletimeexceeded" => "Timpul disponibil a expirat",
        "ConfirmDelete" => "Sigur vrei sa stergi?",
        "SelectLine" => "Selecteaza o linie",
        "SelectDate" => "Selecteaza o data",
        "EmptyData" => "Nu exista date",
        "InvalidFileFormat" => "Format invalid de fisier xls. Incarca un fisier valid .xls | .xlsx",
        "invalidXlsData" => "Date lipsa in celulele A | B",
        "dataReadyForExport" => "Planul este pregatit pentru a fi exportat.",
        "invalidRouting" => "Lipseste routing-ul pentru part-number"
        ]    
    ],
];