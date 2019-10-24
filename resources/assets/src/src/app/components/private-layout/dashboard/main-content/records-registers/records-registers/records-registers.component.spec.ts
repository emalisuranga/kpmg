import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RecordsRegistersComponent } from './records-registers.component';

describe('RecordsRegistersComponent', () => {
  let component: RecordsRegistersComponent;
  let fixture: ComponentFixture<RecordsRegistersComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RecordsRegistersComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RecordsRegistersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
