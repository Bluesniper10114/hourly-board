import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';

import * as fromSharedFuse from '@hourly-board-workspace/shared/fuse';

import { ContentModule } from '../../components/content/content.module';
import { FooterModule } from '../../components/footer/footer.module';
import { NavbarModule } from '../../components/navbar/navbar.module';
import { QuickPanelModule } from '../../components/quick-panel/quick-panel.module';
import { ToolbarModule } from '../../components/toolbar/toolbar.module';

import { VerticalLayout2Component } from '../../vertical/layout-2/layout-2.component';

@NgModule({
    declarations: [
        VerticalLayout2Component
    ],
    imports     : [
        RouterModule,

        fromSharedFuse.SharedFuseModule,
        fromSharedFuse.FuseSidebarModule,

        ContentModule,
        FooterModule,
        NavbarModule,
        QuickPanelModule,
        ToolbarModule
    ],
    exports     : [
        VerticalLayout2Component
    ]
})
export class VerticalLayout2Module
{
}
