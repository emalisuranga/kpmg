import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RecordsRegistersResubmitComponent } from './records-registers-resubmit.component';

describe('RecordsRegistersResubmitComponent', () => {
  let component: RecordsRegistersResubmitComponent;
  let fixture: ComponentFixture<RecordsRegistersResubmitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RecordsRegistersResubmitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RecordsRegistersResubmitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
