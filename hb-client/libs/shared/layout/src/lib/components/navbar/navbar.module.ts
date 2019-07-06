import { NgModule } from '@angular/core';
import { NavbarComponent } from './navbar.component';
import { NavbarHorizontalStyle1Module } from './horizontal/style-1/style-1.module';
import { NavbarVerticalStyle1Module } from './vertical/style-1/style-1.module';
import { NavbarVerticalStyle2Module } from './vertical/style-2/style-2.module';

import * as fromSharedFuse from '@hourly-board-workspace/shared/fuse';
@NgModule({
    declarations: [
        NavbarComponent
    ],
    imports     : [
      fromSharedFuse.SharedFuseModule,

        NavbarHorizontalStyle1Module,
        NavbarVerticalStyle1Module,
        NavbarVerticalStyle2Module
    ],
    exports     : [
        NavbarComponent
    ]
})
export class NavbarModule
{
}
