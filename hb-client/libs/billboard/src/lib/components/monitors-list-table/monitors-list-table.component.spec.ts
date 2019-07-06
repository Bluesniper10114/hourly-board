import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MonitorsListTableComponent } from './monitors-list-table.component';

describe('MonitorsListTableComponent', () => {
  let component: MonitorsListTableComponent;
  let fixture: ComponentFixture<MonitorsListTableComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MonitorsListTableComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MonitorsListTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
