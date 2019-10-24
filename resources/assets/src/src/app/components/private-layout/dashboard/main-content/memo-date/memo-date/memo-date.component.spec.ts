import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MemoDateComponent } from './memo-date.component';

describe('MemoDateComponent', () => {
  let component: MemoDateComponent;
  let fixture: ComponentFixture<MemoDateComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MemoDateComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MemoDateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
