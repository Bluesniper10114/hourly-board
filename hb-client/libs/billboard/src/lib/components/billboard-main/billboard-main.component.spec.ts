import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BillboardMainComponent } from './billboard-main.component';

describe('BillboardMainComponent', () => {
  let component: BillboardMainComponent;
  let fixture: ComponentFixture<BillboardMainComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BillboardMainComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BillboardMainComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
