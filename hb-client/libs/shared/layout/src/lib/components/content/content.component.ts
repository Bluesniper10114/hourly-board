import { Component, ViewEncapsulation } from '@angular/core';

@Component({
    // tslint:disable-next-line:component-selector
    selector     : 'content',
    templateUrl  : './content.component.html',
    styleUrls    : ['./content.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class ContentComponent
{
    /**
     * Constructor
     */
    constructor()
    {
    }
}
