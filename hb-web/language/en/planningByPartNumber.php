<?php
return [
    "PlanningByPart" =>
        [
        "Header" => "Planning by Part Number",
        "Title" => "Planning by Part Number",
        "HeaderLabels" =>
            [
            "Pn" => "PN",
            "Initialquantity" => "Initial<br/>quantity",
            "RoutingASSYPN" => "Routing (minute)",
            "Total" => "Total",
            "A" => "A",
            "B" => "B",
            "C" => "C",
            "IMPORTEXCEL"=> "IMPORT PLAN FROM EXCEL",
            "DISTRIBUTETOTAL"=>"DISTRIBUTE TOTAL",
            "EXPORT" => "EXPORT",
            "Save" => "SAVE",
            "Validate" => 'VALIDATE',
            "Add" => "Add",
            "Delete" => "Delete",
        ],
         
    "Errors" =>
        [
            "Main" => "Please check errors",
            "Timeout" => "Targets will be imported from the latest available shift unless you set them explicitly",
            "Bottom" => "*Please address all the errors above in order to be able to save your progress",
            "Target" => "Machine Capacity exceeded",
            "QuantityMatch" => "Initial quantity does not match target",
            "Availabletimeexceeded" => "Available time exceeded",
            "ConfirmDelete" => "Are you sure to delete",
            "SelectLine" => "Select a line to load.",
            "SelectDate" => "Select date.",
            "EmptyData" => "No Data",
            "InvalidFileFormat" => "Invalid file format supplied. Please upload .xls | .xlsx",
            "invalidXlsData" => "Missing data in A | B cells",
            "dataReadyForExport" => "Data ready for export.",
            "invalidRouting" => "Missing routing for the part-number"
        ],
    ],
];