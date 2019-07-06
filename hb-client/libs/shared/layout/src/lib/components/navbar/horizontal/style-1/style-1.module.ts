import { NgModule } from '@angular/core';
import { MatButtonModule, MatIconModule } from '@angular/material';
import { NavbarHorizontalStyle1Component } from './style-1.component';
import { FuseNavigationModule, SharedFuseModule } from '@hourly-board-workspace/shared/fuse';



@NgModule({
    declarations: [
        NavbarHorizontalStyle1Component
    ],
    imports     : [
        MatButtonModule,
        MatIconModule,

        SharedFuseModule,
        FuseNavigationModule
    ],
    exports     : [
        NavbarHorizontalStyle1Component
    ]
})
export class NavbarHorizontalStyle1Module
{
}
