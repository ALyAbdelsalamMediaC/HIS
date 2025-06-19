@extends('layouts.app')
@section('title', 'HIS | Edit Help')
@section('content')

  <section>
    <div class="gap-3 d-flex align-items-center">
    <div class="arrow-back-btn" onclick="window.history.back()">
      <x-svg-icon name="arrow-left2" size="16" color="#35758C" />
    </div>

    <div>
      <h2 class="h2-semibold" style="color:#35758C;">Edit Help</h2>
      <p class="h5-ragular" style="color:#ADADAD;">Update your help details</p>
    </div>
    </div>

    <form action="{{ route('policies.update', $policy) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-infield">
      <x-text_label for="title" :required="true">Help Title</x-text_label>
      <x-text_input type="text" id="title" name="title" placeholder="Help Title"
      value="{{ old('title', $policy->title) }}" data-required="true" data-name="Help Title" />
      <div id="title-error-container">
      <x-input-error :messages="$errors->get('title')" />
      </div>
    </div>

    <div class="gap-3 d-flex align-items-end justify-content-between w-100">
      <div class="form-infield" style="width: 75%;">
      <x-text_label for="category" :required="true">Help Category</x-text_label>
      <x-select id="category" name="category_id" :options="$categories->mapWithKeys(function ($category) {
    return [$category->id => $category->title];
    })->all()" placeholder="Select Category" data-required="true"
        data-name="Help Category" :selected="old('category_id', $policy->category_id)" />
      <div id="category-error-container">
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
      </div>
      </div>
      <div class="gap-2 d-flex align-items-center" style="width: 25%;">
      <x-button type="button" data-bs-toggle="modal" data-bs-target="#editCategoriesModal" id="editCategoryBtn">
        Edit Categories
      </x-button>
      <x-button type="button" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <x-svg-icon name="plus3" size="16" />
        Add Category
      </x-button>
      </div>
    </div>

    <div class="mt-3 form-infield">
      <x-text_label for="body" :required="true">Help Description</x-text_label>
      <x-textarea id="body" name="body" class="form-control" placeholder="Help body" data-required="true"
      data-name="Help body" value="{{ old('body', $policy->body) }}" />
      <div id="body-error-container">
      <x-input-error :messages="$errors->get('body')" />
      </div>
    </div>

    <div class="gap-3 mt-4 d-flex justify-content-end align-items-center">
      <x-link_btn href="{{ route('policies.index') }}" class="px-4 bg-trans-btn">Cancel</x-link_btn>
      <x-button type="submit" class="px-4">Update Help</x-button>
    </div>
    </form>

    <!-- Add Category Modal -->
    <x-modal id="addCategoryModal" title="Add Category">
    <form action="{{ route('policies.categories.store') }}" method="POST" id="addCategoryForm" class="mt-4" novalidate>
      @csrf
      <div class="form-infield">
      <x-text_label for="modal_category_title" :required="true">Category Title</x-text_label>
      <x-text_input type="text" id="modal_category_title" name="category_title" placeholder="Category Title"
        value="{{ old('category_title') }}" data-required="true" data-name="Category Title" autocomplete="off" />
      <div id="modal_category_title-error-container">
        <x-input-error :messages="$errors->get('category_title')" />
      </div>
      </div>
      <div class="mt-3 d-flex justify-content-end">
      <x-button type="button" class="bg-trans-btn me-2" data-bs-dismiss="modal">Cancel</x-button>
      <x-button type="submit">Add Category</x-button>
      </div>
    </form>
    </x-modal>

    <!-- Edit Categories Modal -->
    <x-modal id="editCategoriesModal" title="Edit Categories">
    <div class="categories-list">
      @forelse($categories as $category)
      <div class="p-2 category-item d-flex align-items-center justify-content-between border-bottom">
      <div class="category-title d-flex align-items-center flex-grow-1" data-id="{{ $category->id }}">
      <span class="category-text">{{ $category->title }}</span>
      <form action="{{ route('policies.categories.update', $category) }}" method="POST"
      class="edit-category-form d-none w-100">
      @csrf
      @method('PUT')
      <div class="d-flex">
        <x-text_input type="text" name="title" placeholder="Category title" class="category-input"
        value="{{ $category->title }}" />
      </div>
      </form>
      </div>
      <div class="mx-2 category-actions">
      <button type="button" class="p-0 border-0 btn btn-link edit-category-btn me-2" data-id="{{ $category->id }}">
      <x-svg-icon name="edit-pen2" size="18" color="#adadad" />
      </button>
      <button type="button" class="p-0 border-0 btn btn-link delete-category-btn" data-bs-toggle="modal"
      data-bs-target="#deleteCategoryModal{{ $category->id }}">
      <x-svg-icon name="trash" size="18" color="#adadad" />
      </button>
      </div>
      </div>
    @empty
      <p class="p-3 text-center">No categories found.</p>
    @endforelse
    </div>
    <div class="mt-3 d-flex justify-content-end">
      <x-button type="button" class="bg-trans-btn" data-bs-dismiss="modal">Close</x-button>
    </div>
    </x-modal>

    <!-- Delete Category Modals -->
    @foreach($categories as $category)
    <x-modal id="deleteCategoryModal{{ $category->id }}" title="Delete Category">
    <div class="my-3">
      <p class="h3-semibold" style="color:black;">Are you sure you want to delete the category
      "{{ $category->title }}"?</p>
    </div>
    <div class="modal-footer">
      <x-button type="button" style="color:#BB1313; background-color:transparent; border:1px solid #BB1313;"
      data-bs-dismiss="modal">Cancel</x-button>
      <form action="{{ route('policies.categories.destroy', $category) }}" method="POST">
      @csrf
      @method('DELETE')
      <x-button type="submit" style="background-color:#BB1313; color:#fff;">Delete</x-button>
      </form>
    </div>
    </x-modal>
    @endforeach
  </section>

@endsection

@push('scripts')
  <script src="{{ asset('js/validations.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
    // Handle edit button clicks
    const editButtons = document.querySelectorAll('.edit-category-btn');
    editButtons.forEach(button => {
      button.addEventListener('click', function () {
      const categoryId = this.getAttribute('data-id');
      const categoryItem = document.querySelector(`.category-title[data-id="${categoryId}"]`);
      const textElement = categoryItem.querySelector('.category-text');
      const formElement = categoryItem.querySelector('.edit-category-form');

      // Hide text, show form
      textElement.classList.add('d-none');
      formElement.classList.remove('d-none');

      // Focus on input
      const inputElement = formElement.querySelector('.category-input');
      inputElement.focus();
      inputElement.select();

      // Add event listener for enter key to submit
      inputElement.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
        e.preventDefault();
        formElement.submit();
        } else if (e.key === 'Escape') {
        textElement.classList.remove('d-none');
        formElement.classList.add('d-none');
        }
      });
      });
    });
    });
  </script>
@endpush