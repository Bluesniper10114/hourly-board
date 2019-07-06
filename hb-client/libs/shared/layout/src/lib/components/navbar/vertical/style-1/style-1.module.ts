import { NgModule } from '@angular/core';
import { MatButtonModule, MatIconModule } from '@angular/material';
import { NavbarVerticalStyle1Component } from './style-1.component';
import { SharedFuseModule, FuseNavigationModule } from '@hourly-board-workspace/shared/fuse';



@NgModule({
    declarations: [
        NavbarVerticalStyle1Component
    ],
    imports     : [
        MatButtonModule,
        MatIconModule,

        SharedFuseModule,
        FuseNavigationModule
    ],
    exports     : [
        NavbarVerticalStyle1Component
    ]
})
export class NavbarVerticalStyle1Module
{
}
