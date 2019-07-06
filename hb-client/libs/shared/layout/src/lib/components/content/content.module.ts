import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import * as fromSharedFuse from '@hourly-board-workspace/shared/fuse';


import { ContentComponent } from './content.component';

@NgModule({
    declarations: [
        ContentComponent
    ],
    imports     : [
        RouterModule,
        fromSharedFuse.SharedFuseModule
    ],
    exports: [
        ContentComponent
    ]
})
export class ContentModule
{
}
