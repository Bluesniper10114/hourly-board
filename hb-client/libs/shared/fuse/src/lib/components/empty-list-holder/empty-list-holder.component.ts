import { Component, OnInit, Input } from '@angular/core';
import { fuseAnimations } from '../../animations';

@Component({
  selector: 'fuse-empty-list-holder',
  templateUrl: './empty-list-holder.component.html',
  styleUrls: ['./empty-list-holder.component.scss'],
  animations: fuseAnimations
})
export class EmptyListHolderComponent implements OnInit {
  @Input() icon: string;
  @Input() title: string;
  @Input() text: string;
  constructor() {}

  ngOnInit() {}
}
